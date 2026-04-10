<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Shows claimed/disposed items history.
 * Ported from ADMIN/HistoryAdmin.php.
 * Read-only — no write actions.
 */
class HistoryController extends Controller
{
    /** GET /admin/history */
    public function index(Request $request)
    {
        $categoryFilter = $request->query('category');
        $dateFilter     = $request->query('date_filter');

        $dateRange = $this->resolveDateRange($dateFilter);

        // Internal: found items (non-ID & Nameplate) that have been claimed or disposed
        $allClaimed = Item::foundItems()
            ->where('item_type', '!=', 'ID & Nameplate')
            ->whereIn('status', ['Claimed', 'Disposed'])
            ->when($categoryFilter, fn($q) => $q->where('item_type', $categoryFilter))
            ->when($dateRange['from'], fn($q) => $q->where('updated_at', '>=', $dateRange['from']))
            ->when($dateRange['to'],   fn($q) => $q->where('updated_at', '<=', $dateRange['to']))
            ->orderByDesc('updated_at')
            ->get();

        // External: guest ID items (ID & Nameplate) that have been claimed or disposed
        $guestClaimed = Item::foundItems()
            ->where('item_type', 'ID & Nameplate')
            ->whereIn('status', ['Claimed', 'Disposed'])
            ->when($dateRange['from'], fn($q) => $q->where('updated_at', '>=', $dateRange['from']))
            ->when($dateRange['to'],   fn($q) => $q->where('updated_at', '<=', $dateRange['to']))
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.history', compact('allClaimed', 'guestClaimed', 'categoryFilter', 'dateFilter'));
    }

    private function resolveDateRange(?string $filter): array
    {
        $now = Carbon::now();
        return match ($filter) {
            'today'   => ['from' => $now->toDateString(),                                'to' => $now->toDateString()],
            'week'    => ['from' => $now->copy()->startOfWeek()->toDateString(),          'to' => $now->toDateString()],
            'month'   => ['from' => $now->copy()->startOfMonth()->toDateString(),         'to' => $now->toDateString()],
            '3months' => ['from' => $now->copy()->subMonths(3)->toDateString(),           'to' => $now->toDateString()],
            'year'    => ['from' => $now->copy()->startOfYear()->toDateString(),          'to' => $now->toDateString()],
            default   => ['from' => null, 'to' => null],
        };
    }
}
