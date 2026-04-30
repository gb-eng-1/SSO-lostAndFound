<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function csvResponse(string $filename, callable $write): Response
    {
        $out = fopen('php://temp', 'r+b');
        $write($out);
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

    // ── Recovered Items (Internal) ─────────────────────────────────────────────

    public function internalItems(): Response
    {
        $filename = 'UB_Internal_Items_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return $this->csvResponse($filename, function ($out) {
            fputcsv($out, ['Barcode ID', 'Category', 'Item', 'Color', 'Brand', 'Status',
                           'Found At', 'Storage Location', 'Date Found', 'Retention End', 'Created At']);

            Item::foundItems()
                ->where('item_type', '!=', 'Lost ID (Guest)')
                ->whereNotIn('status', ['Cancelled', 'Disposed'])
                ->orderByDesc('created_at')
                ->chunk(200, function ($items) use ($out) {
                    foreach ($items as $item) {
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
                            $item->retentionEndDate() ? $item->retentionEndDate()->format('Y-m-d') : '',
                            $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : '',
                        ]);
                    }
                });
        });
    }

    // ── Recovered IDs (External) ───────────────────────────────────────────────

    public function externalIds(): Response
    {
        $filename = 'UB_External_IDs_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return $this->csvResponse($filename, function ($out) {
            fputcsv($out, ['Barcode ID', 'Category', 'Full Name', 'Color', 'Encoded By',
                           'Storage Location', 'Date Surrendered', 'Retention End', 'Status', 'Created At']);

            Item::foundItems()
                ->where('item_type', 'Lost ID (Guest)')
                ->whereNotIn('status', ['Cancelled', 'Disposed'])
                ->orderByDesc('created_at')
                ->chunk(200, function ($items) use ($out) {
                    foreach ($items as $item) {
                        $parsed = $item->parseDescription();
                        fputcsv($out, [
                            $item->id,
                            $item->item_type ?? '',
                            $parsed['Full Name'] ?? '',
                            $item->color ?? '',
                            $item->found_by ?? '',
                            $item->storage_location ?? '',
                            $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '',
                            $item->retentionEndDate() ? $item->retentionEndDate()->format('Y-m-d') : '',
                            $item->status ?? '',
                            $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : '',
                        ]);
                    }
                });
        });
    }

    // ── Unresolved Claimants ───────────────────────────────────────────────────

    public function unresolvedClaimants(): Response
    {
        $filename = 'UB_Unresolved_Claimants_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return $this->csvResponse($filename, function ($out) {
            fputcsv($out, ['Ticket ID', 'Category', 'Full Name', 'Department', 'Student ID',
                           'Contact', 'Color', 'Brand', 'Status', 'Date Lost', 'Created At']);

            Item::lostReports()
                ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
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
                            $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : '',
                        ]);
                    }
                });
        });
    }

    // ── For Verification ───────────────────────────────────────────────────────

    public function forVerification(): Response
    {
        $filename = 'UB_For_Verification_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return $this->csvResponse($filename, function ($out) {
            fputcsv($out, ['Barcode ID', 'Category', 'Item', 'Color', 'Brand', 'Status',
                           'Found At', 'Storage Location', 'Date Found', 'Retention End', 'Created At']);

            Item::foundItems()
                ->whereIn('status', ['For Verification', 'Matched', 'Unresolved Claimants'])
                ->orderByDesc('created_at')
                ->chunk(200, function ($items) use ($out) {
                    foreach ($items as $item) {
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
                            $item->retentionEndDate() ? $item->retentionEndDate()->format('Y-m-d') : '',
                            $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : '',
                        ]);
                    }
                });
        });
    }
}
