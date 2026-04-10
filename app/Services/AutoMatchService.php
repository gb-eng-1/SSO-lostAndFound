<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Automatically links the best-scoring found item to each unmatched lost report,
 * then notifies the student who filed the report.
 *
 * Scores are computed by MatchScoringService.
 * Threshold: MatchScoringService::MIN_SCORE (default 40 / 100).
 */
class AutoMatchService
{
    public function __construct(private MatchScoringService $scorer) {}

    /**
     * Run auto-matching for a specific lost report against all eligible found items.
     * Called immediately after a student files a new report.
     */
    public function runForReport(Item $lostReport): void
    {
        if (!$this->isEligibleReport($lostReport)) {
            return;
        }

        $foundItems = $this->eligibleFoundItems()->get();
        $this->matchReportToPool($lostReport, $foundItems);
    }

    /**
     * Run auto-matching for a specific newly-encoded found item against all unmatched reports.
     * Links the single best-scoring lost report at or above MIN_SCORE.
     * Called immediately after admin encodes a found item.
     *
     * @return string|null  Lost report id (REF-…) when linked, otherwise null
     */
    public function runForFoundItem(Item $foundItem): ?string
    {
        if (!$this->isEligibleFoundItem($foundItem)) {
            return null;
        }

        $reports = $this->eligibleLostReports()->get();
        $bestReport = null;
        $bestScore = 0;

        foreach ($reports as $report) {
            $score = $this->scorer->score($report, $foundItem);
            if ($score > $bestScore && $score >= MatchScoringService::MIN_SCORE) {
                $bestReport = $report;
                $bestScore = $score;
            }
        }

        if ($bestReport) {
            $this->link($bestReport, $foundItem);

            return $bestReport->id;
        }

        return null;
    }

    /**
     * Batch pass: link all currently-unmatched pairs above the threshold.
     * Used by the scheduled artisan command (matches:run).
     */
    public function runAll(): int
    {
        $linked = 0;

        $reports    = $this->eligibleLostReports()->get();
        $foundItems = $this->eligibleFoundItems()->get();

        foreach ($reports as $report) {
            $best      = null;
            $bestScore = 0;

            foreach ($foundItems as $found) {
                $score = $this->scorer->score($report, $found);
                if ($score > $bestScore && $score >= MatchScoringService::MIN_SCORE) {
                    $best      = $found;
                    $bestScore = $score;
                }
            }

            if ($best) {
                $this->link($report, $best);
                $linked++;
                // Remove the matched item from further consideration
                $foundItems = $foundItems->reject(fn($f) => $f->id === $best->id)->values();
            }
        }

        return $linked;
    }

    /**
     * Pick the best-scoring found item for one lost report (same logic as one iteration of runAll).
     *
     * @param  \Illuminate\Support\Collection<int, Item>|iterable<Item>  $foundItems
     */
    private function matchReportToPool(Item $lostReport, iterable $foundItems): void
    {
        $best = null;
        $bestScore = 0;

        foreach ($foundItems as $found) {
            $score = $this->scorer->score($lostReport, $found);
            if ($score > $bestScore && $score >= MatchScoringService::MIN_SCORE) {
                $best = $found;
                $bestScore = $score;
            }
        }

        if ($best) {
            $this->link($lostReport, $best);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function eligibleLostReports()
    {
        return Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->whereNull('matched_barcode_id')
            ->orderByDesc('date_lost');
    }

    private function eligibleFoundItems()
    {
        return Item::foundItems()
            ->where('item_type', '!=', 'ID & Nameplate')
            ->where('status', 'Unclaimed Items')
            ->orderByDesc('date_encoded');
    }

    private function isEligibleReport(Item $item): bool
    {
        return str_starts_with($item->id, 'REF-')
            && !in_array($item->status, ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            && empty($item->matched_barcode_id);
    }

    private function isEligibleFoundItem(Item $item): bool
    {
        return !str_starts_with($item->id, 'REF-')
            && $item->item_type !== 'ID & Nameplate'
            && $item->status === 'Unclaimed Items';
    }

    private function link(Item $report, Item $found): void
    {
        try {
            DB::transaction(function () use ($report, $found) {
                $found->update(['status' => 'For Verification']);

                $report->update([
                    'status'             => 'For Verification',
                    'matched_barcode_id' => $found->id,
                ]);

                if (Schema::hasTable('item_matches')) {
                    DB::table('item_matches')->insertOrIgnore([
                        'found_item_id'  => $found->id,
                        'lost_report_id' => $report->id,
                        'linked_at'      => now(),
                    ]);
                }

                $this->notifyStudent($report, $found);

                Notification::notifyAdmin(
                    'item_matched',
                    'Auto-Match Found',
                    "Lost report {$report->id} was automatically matched to found item {$found->id}.",
                    $found->id
                );

                ActivityLog::record(
                    'matched',
                    $found->id,
                    "Auto-linked to {$report->id}",
                    null,
                    'system'
                );
            });
        } catch (Throwable) {
            // Non-fatal: matching failure must not break the parent request
        }
    }

    private function notifyStudent(Item $report, Item $found): void
    {
        if (empty($report->user_id)) {
            return;
        }

        $student = Student::where('email', $report->user_id)->first();
        if (!$student) {
            return;
        }

        $itemLabel = trim($found->item_type ?? 'item');

        Notification::notifyStudent(
            $student->id,
            'item_matched',
            'Potential Match Found!',
            "A potential match has been found for your lost report {$report->id}. "
            . "A {$itemLabel} ({$found->id}) may be your item. "
            . 'The pair is under For Verification; staff will guide you to complete claiming. '
            . 'Please visit the office to verify.',
            $report->id
        );
    }
}
