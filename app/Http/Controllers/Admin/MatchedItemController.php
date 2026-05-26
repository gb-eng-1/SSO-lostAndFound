<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Manages the Matched Items / For-Claiming admin page.
 * Matching is now handled automatically by AutoMatchService.
 * This controller is read-only: it lists items ready to be claimed.
 */
class MatchedItemController extends Controller
{
    /** GET /admin/matched */
    public function index()
    {
        // Internal: non-ID found items that have been auto-matched or have an acknowledged claim
        $matchedLostReports = Schema::hasTable('item_matches')
            ? DB::table('item_matches')->pluck('lost_report_id', 'found_item_id')->toArray()
            : [];

        $foundItems = Item::foundItems()
            ->whereIn('status', ['For Verification', 'Unresolved Claimants'])
            ->where('item_type', '!=', 'ID & Nameplate')
            ->orderByDesc('date_encoded')
            ->get()
            ->map(function ($item) use ($matchedLostReports) {
                $item = $this->attachRetention($item, 2);
                // 'Unresolved Claimants' means the student already acknowledged — never gate
                $item->admin_claim_gated = $item->status === 'Unresolved Claimants'
                    ? false
                    : ($item->requiresStudentClaimIntentBeforeAdminClaim()
                        && ! $item->hasStudentClaimIntentForAdminClaim());

                // Attach linked lost report ID for the CCM modal student report card
                $item->linked_lost_report_id = $matchedLostReports[$item->id] ?? null;

                return $item;
            });

        // External: guest ID items that have been matched or are ready to claim
        $guestItems = Item::foundItems()
            ->where('item_type', 'ID & Nameplate')
            ->where('status', 'For Verification')
            ->orderByDesc('date_encoded')
            ->get()
            ->map(function ($item) {
                $item = $this->attachRetention($item, 1);
                $item->admin_claim_gated = false;

                return $item;
            });

        // Items resolved this month
        $resolvedThisMonth = DB::table('activity_log')
            ->where('action', 'claimed')
            ->whereRaw('MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())')
            ->count();

        return view('admin.matched-items', compact(
            'foundItems',
            'guestItems',
            'resolvedThisMonth'
        ));
    }

    private function attachRetention(Item $item, int $years = 2): Item
    {
        $end = $item->retentionEndDate();
        if ($end) {
            $item->retention_end      = $end->toDateString();
            $item->is_overdue         = $end->isPast();
            $item->expires_in_30_days = !$item->is_overdue && $end->diffInDays(now(), false) >= -30;
        }
        return $item;
    }
}
