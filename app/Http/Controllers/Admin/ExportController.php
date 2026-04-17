<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /**
     * GET /admin/export/dashboard — CSV export of the current database snapshot.
     */
    public function dashboard(): Response
    {
        $filename = 'UB_LostFound_Export_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $out = fopen('php://temp', 'r+b');

        // -- Summary --
        fputcsv($out, ['=== SUMMARY ===']);
        fputcsv($out, ['Metric', 'Count']);

        $internalRecovered = Item::foundItems()
            ->where('item_type', '!=', 'Lost ID (Guest)')
            ->whereNotIn('status', ['Cancelled', 'Disposed'])
            ->count();

        $externalIds = Item::foundItems()
            ->where('item_type', 'Lost ID (Guest)')
            ->whereNotIn('status', ['Cancelled', 'Disposed'])
            ->count();

        $unresolved = Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->count();

        $forVerification = DB::table('item_matches')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('items')
                  ->whereColumn('items.id', 'item_matches.found_barcode_id')
                  ->whereIn('items.status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed']);
            })
            ->count();

        fputcsv($out, ['Recovered Items (Internal)', $internalRecovered]);
        fputcsv($out, ['Recovered IDs (External)', $externalIds]);
        fputcsv($out, ['Unresolved Claimants', $unresolved]);
        fputcsv($out, ['For Verification', $forVerification]);

        // -- Status distribution (for charts) --
        fputcsv($out, []);
        fputcsv($out, ['=== STATUS DISTRIBUTION ===']);
        fputcsv($out, ['Status', 'Found Items Count', 'Lost Reports Count']);

        $foundStatuses = Item::foundItems()
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $lostStatuses = Item::lostReports()
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $allStatuses = array_unique(array_merge(array_keys($foundStatuses), array_keys($lostStatuses)));
        sort($allStatuses);

        foreach ($allStatuses as $status) {
            fputcsv($out, [$status, $foundStatuses[$status] ?? 0, $lostStatuses[$status] ?? 0]);
        }

        // -- Found Items --
        fputcsv($out, []);
        fputcsv($out, ['=== FOUND ITEMS ===']);
        fputcsv($out, ['Barcode ID', 'Category', 'Item', 'Color', 'Brand', 'Status', 'Found At', 'Storage Location', 'Date Found', 'Retention End', 'Created At']);

        Item::foundItems()
            ->orderByDesc('created_at')
            ->chunk(200, function ($items) use ($out) {
                foreach ($items as $item) {
                    $retEnd = $item->retentionEndDate();
                    fputcsv($out, [
                        $item->id,
                        $item->item_type ?? '',
                        $item->parseDescription()['Item'] ?? '',
                        $item->color ?? '',
                        $item->brand ?? '',
                        $item->status ?? '',
                        $item->found_at ?? '',
                        $item->storage_location ?? '',
                        $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '',
                        $retEnd ? $retEnd->format('Y-m-d') : '',
                        $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : '',
                    ]);
                }
            });

        // -- Lost Reports --
        fputcsv($out, []);
        fputcsv($out, ['=== LOST REPORTS ===']);
        fputcsv($out, ['Ticket ID', 'Category', 'Full Name', 'Department', 'Student ID', 'Contact', 'Color', 'Brand', 'Status', 'Date Lost', 'Matched Item', 'Created At']);

        Item::lostReports()
            ->orderByDesc('created_at')
            ->chunk(200, function ($items) use ($out) {
                foreach ($items as $item) {
                    $parsed = $item->parseDescription();
                    fputcsv($out, [
                        $item->display_ticket_id,
                        $item->item_type ?? '',
                        $parsed['Full Name'] ?? '',
                        $parsed['Department'] ?? '',
                        $parsed['Student Number'] ?? '',
                        $parsed['Contact'] ?? '',
                        $item->color ?? '',
                        $item->brand ?? '',
                        $item->status ?? '',
                        $item->date_lost ? $item->date_lost->format('Y-m-d') : '',
                        $item->matched_barcode_id ?? '',
                        $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : '',
                    ]);
                }
            });

        // -- Claims --
        fputcsv($out, []);
        fputcsv($out, ['=== CLAIMS ===']);
        fputcsv($out, ['Reference ID', 'Student ID', 'Found Item', 'Lost Report', 'Status', 'Created At']);

        Claim::orderByDesc('created_at')
            ->chunk(200, function ($claims) use ($out) {
                foreach ($claims as $claim) {
                    fputcsv($out, [
                        $claim->reference_id ?? '',
                        $claim->student_id ?? '',
                        $claim->found_item_id ?? '',
                        $claim->lost_report_id ?? '',
                        $claim->status ?? '',
                        $claim->created_at ? Carbon::parse($claim->created_at)->format('Y-m-d H:i') : '',
                    ]);
                }
            });

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
