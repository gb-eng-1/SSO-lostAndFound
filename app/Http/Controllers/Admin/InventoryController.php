<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryController extends Controller
{
    private const INTERNAL_RETENTION_YEARS = 2;

    private const CATEGORIES = [
        'Electronics & Gadgets',
        'Document & Identification',
        'Personal Belongings',
        'Apparel & Accessories',
        'Miscellaneous',
    ];

    /** GET /admin/inventory */
    public function index(Request $request)
    {
        $category = $request->query('category');

        $items = Item::foundItems()
            ->where(function ($q) {
                $q->whereNull('item_type')
                  ->orWhereNotIn('item_type', ['ID & Nameplate', 'Document & Identification']);
            })
            ->where('status', 'Unclaimed Items')
            ->when($category, fn ($q) => $q->where('item_type', $category))
            ->orderByDesc('date_encoded')
            ->get()
            ->map(fn ($item) => $this->attachRetention($item));

        $overdueCount = $items->where('is_overdue', true)->count();

        return view('admin.inventory', [
            'items'        => $items,
            'overdueCount' => $overdueCount,
            'categories'   => self::CATEGORIES,
            'category'     => $category,
        ]);
    }

    /** GET /admin/inventory/item-detail/{id} — AJAX */
    public function itemDetail(string $id): JsonResponse
    {
        $item = Item::foundItems()->where('id', $id)->first();
        if (!$item) {
            return response()->json(['ok' => false, 'error' => 'Item not found.'], 404);
        }

        $parsed = $item->parseDescription();

        // Find unmatched lost reports with the same item_type as potential claimants
        $claimants = Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->whereNull('matched_barcode_id')
            ->when($item->item_type, fn ($q) => $q->where('item_type', $item->item_type))
            ->orderByDesc('date_lost')
            ->limit(20)
            ->get()
            ->map(fn ($report) => [
                'id'    => $report->id,
                'email' => $report->user_id ?? '',
            ])
            ->filter(fn ($c) => $c['email'] !== '')
            ->values();

        // Strip embedded metadata lines from description to get the free-text portion
        $rawDesc    = $item->item_description ?? '';
        $metaKeys   = ['Student Number', 'Full Name', 'Contact', 'Department', 'Item Type',
                       'Owner', 'ID Type', 'Item', 'Encoded By', 'Found By'];
        $cleanLines = [];
        foreach (explode("\n", $rawDesc) as $line) {
            $isMeta = false;
            foreach ($metaKeys as $key) {
                if (str_starts_with(trim($line), $key . ':')) {
                    $isMeta = true;
                    break;
                }
            }
            if (!$isMeta) {
                $cleanLines[] = $line;
            }
        }
        $itemDescription = trim(implode("\n", $cleanLines));

        return response()->json([
            'ok'               => true,
            'id'               => $item->id,
            'item_type'        => $item->item_type ?? '',
            'item_name'        => $parsed['Item'] ?? '',
            'color'            => $item->color ?? '',
            'brand'            => $item->brand ?? '',
            'item_description' => $itemDescription,
            'storage_location' => $item->storage_location ?? '',
            'found_at'         => $item->found_at ?? '',
            'found_by'         => $parsed['Found By'] ?? ($item->found_by ?? ''),
            'encoded_by'       => $parsed['Encoded By'] ?? '',
            'date_found'       => $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '',
            'image_data'       => $item->image_data ?? null,
            'claimants'        => $claimants,
        ]);
    }

    /** POST /admin/inventory/confirm/{id} — AJAX: manually link to a lost report */
    public function confirm(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'lost_report_id' => 'required|string',
        ]);

        $foundItem  = Item::foundItems()->where('id', $id)->where('status', 'Unclaimed Items')->first();
        $lostReport = Item::lostReports()->where('id', $request->lost_report_id)->first();

        if (!$foundItem) {
            return response()->json(['ok' => false, 'error' => 'Found item not found or already processed.'], 404);
        }
        if (!$lostReport) {
            return response()->json(['ok' => false, 'error' => 'Lost report not found.'], 404);
        }
        if ($lostReport->matched_barcode_id) {
            return response()->json(['ok' => false, 'error' => 'This claimant is already matched to another item.'], 422);
        }

        DB::transaction(function () use ($foundItem, $lostReport) {
            $foundItem->update(['status' => 'For Verification']);

            $lostReport->update([
                'status'             => 'For Verification',
                'matched_barcode_id' => $foundItem->id,
            ]);

            if (Schema::hasTable('item_matches')) {
                DB::table('item_matches')->insertOrIgnore([
                    'found_item_id'  => $foundItem->id,
                    'lost_report_id' => $lostReport->id,
                    'linked_at'      => now(),
                ]);
            }

            // Notify the student whose lost report was matched
            if ($lostReport->user_id) {
                $student = Student::where('email', $lostReport->user_id)->first();
                if ($student) {
                    $itemLabel = trim($foundItem->item_type ?? 'item');
                    Notification::notifyStudent(
                        $student->id,
                        'item_matched',
                        'Potential Match Found!',
                        "A potential match has been found for your lost report {$lostReport->id}. "
                        . "A {$itemLabel} ({$foundItem->id}) may be your item. "
                        . 'Please visit the SSO office to verify.',
                        $lostReport->id
                    );
                }
            }

            Notification::notifyAdmin(
                'item_matched',
                'Manual Match Confirmed',
                "Found item {$foundItem->id} was manually matched to lost report {$lostReport->id}.",
                $foundItem->id
            );

            ActivityLog::record(
                'matched',
                $foundItem->id,
                "Manually linked to {$lostReport->id}",
                session('admin_id'),
                'admin'
            );
        });

        return response()->json(['ok' => true]);
    }

    private function attachRetention(Item $item): Item
    {
        $base = $item->date_encoded ?? $item->created_at;
        if ($base) {
            $retentionEnd = Carbon::parse($base)->addYears(self::INTERNAL_RETENTION_YEARS);
            $item->retention_end      = $retentionEnd->toDateString();
            $item->is_overdue         = $retentionEnd->isPast();
            $item->expires_in_30_days = !$item->is_overdue && $retentionEnd->isFuture() && now()->diffInDays($retentionEnd, false) <= 30;
        }
        return $item;
    }
}
