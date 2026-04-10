<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Services\ItemPurgeService;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shows all lost reports (REF- items) for admin review.
 * Ported from ADMIN/AdminReports.php.
 */
class ReportsController extends Controller
{
    private const CATEGORIES = [
        'Electronics & Gadgets',
        'Document & Identification',
        'Personal Belongings',
        'Apparel & Accessories',
        'Miscellaneous',
    ];

    /** GET /admin/reports */
    public function index(Request $request)
    {
        $categoryFilter = $request->query('category');
        $search         = $request->query('search');

        $reports = Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->when($categoryFilter, fn($q) => $q->where('item_type', $categoryFilter))
            ->when($search, fn($q) => $q->where(function ($inner) use ($search) {
                $inner->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('user_id', 'LIKE', "%{$search}%")
                      ->orWhere('item_description', 'LIKE', "%{$search}%");
            }))
            ->orderByDesc('date_lost')
            ->paginate(30);

        // Parse embedded metadata for display
        $reports->getCollection()->transform(function (Item $item) {
            $meta = $item->parseDescription();
            $fn = $meta['Full Name'] ?? null;
            if ($fn === null || $fn === '') {
                $fn = Student::where('email', $item->user_id)->value('name');
            }
            $item->parsed_full_name      = $fn ?: null;
            $item->parsed_student_number = $meta['Student Number'] ?? null;
            $item->parsed_contact        = $meta['Contact'] ?? null;
            $item->parsed_department     = $meta['Department'] ?? null;
            $item->parsed_item           = $meta['Item'] ?? $meta['Item Type'] ?? null;
            return $item;
        });

        // Recent Activity: lost + matched only (no "Found Item!")
        $recentActivity = $this->buildRecentActivity();

        $categories = self::CATEGORIES;

        return view('admin.reports', compact(
            'reports',
            'categoryFilter',
            'search',
            'recentActivity',
            'categories'
        ));
    }

    /**
     * Build recent activity for Reports page: lost reports + matches only.
     * Excludes claimed items (they appear in History).
     */
    private function buildRecentActivity(): array
    {
        $activities = [];

        // Lost: recent REF- reports (exclude Claimed/Resolved/Cancelled/Disposed)
        $lostItems = Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'item_type', 'created_at']);

        foreach ($lostItems as $item) {
            $activities[] = [
                'action'     => 'lost',
                'item_id'    => $item->id,
                'item_name'  => trim($item->item_type ?? ''),
                'created_at' => $item->created_at,
            ];
        }

        // Matched: REF- items that were linked (from item_matches)
        $matchedRows = collect();
        if (Schema::hasTable('item_matches')) {
            $matchedRows = DB::table('item_matches')
                ->join('items', 'items.id', '=', 'item_matches.lost_report_id')
                ->where('items.id', 'LIKE', 'REF-%')
                ->whereNotIn('items.status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
                ->select('item_matches.lost_report_id as id', 'items.item_type', 'item_matches.linked_at as created_at')
                ->orderByDesc('item_matches.linked_at')
                ->limit(8)
                ->get();
        }

        foreach ($matchedRows as $row) {
            $activities[] = [
                'action'     => 'matched',
                'item_id'    => $row->id,
                'item_name'  => trim($row->item_type ?? ''),
                'created_at' => $row->created_at,
            ];
        }

        // Sort combined by created_at DESC, keep 8
        usort($activities, fn($a, $b) => strcmp(
            (string) ($b['created_at'] ?? ''),
            (string) ($a['created_at'] ?? '')
        ));

        return array_slice($activities, 0, 8);
    }

    /**
     * POST /admin/reports/{id}/cancel — cancel a lost report (admin).
     */
    public function cancel(string $id): JsonResponse
    {
        if (! str_starts_with($id, 'REF-')) {
            return response()->json(['ok' => false, 'error' => 'Invalid report id.'], 422);
        }

        $report = Item::lostReports()
            ->where('id', $id)
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->first();

        if (! $report) {
            return response()->json(['ok' => false, 'error' => 'Report not found or already closed.'], 404);
        }

        (new ItemPurgeService)->purge($id);

        return response()->json(['ok' => true]);
    }
}
