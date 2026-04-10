<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoints for student lost reports.
 * Ported from api/routes/reports.php.
 */
class StudentReportController extends Controller
{
    /** GET /api/student/reports — list this student's lost reports */
    public function index(Request $request): JsonResponse
    {
        $studentEmail = session('student_email');
        if (!$studentEmail) {
            return response()->json(['ok' => false, 'error' => 'Not authenticated.'], 401);
        }

        $reports = Item::lostReports()
            ->where('user_id', $studentEmail)
            ->orderByDesc('date_lost')
            ->get();

        return response()->json(['ok' => true, 'data' => $reports]);
    }

    /** GET /api/student/reports/{id} — fetch a specific report */
    public function show(string $id): JsonResponse
    {
        $studentEmail = session('student_email');

        $report = Item::lostReports()
            ->where('id', $id)
            ->where('user_id', $studentEmail)
            ->first();

        if (!$report) {
            return response()->json(['ok' => false, 'error' => 'Report not found.'], 404);
        }

        $matched = null;
        if ($report->matched_barcode_id) {
            $matched = Item::find($report->matched_barcode_id);
        }

        return response()->json(['ok' => true, 'data' => $report, 'matched_item' => $matched]);
    }

    /**
     * Return scored found-item candidates for a given REF- report.
     * Mirrors get_matching_found_items.php logic.
     * POST /api/student/reports/match-candidates
     */
    public function matchCandidates(Request $request): JsonResponse
    {
        $id = trim($request->input('id', $request->query('id', '')));
        if (!$id) {
            return response()->json(['ok' => false, 'error' => 'Missing report id'], 400);
        }

        $report = Item::lostReports()->find($id);
        if (!$report) {
            return response()->json(['ok' => false, 'error' => 'Report not found'], 404);
        }

        $candidates = Item::foundItems()
            ->whereIn('status', ['Unclaimed Items', 'For Verification'])
            ->get();

        $scored = $candidates->map(function (Item $found) use ($report) {
            $score = $this->scoreMatch($report, $found);
            return $score > 0 ? [
                'id'               => $found->id,
                'found_at'         => $found->found_at,
                'storage_location' => $found->storage_location,
                'score'            => $score,
            ] : null;
        })->filter()->sortByDesc('score')->values();

        return response()->json(['ok' => true, 'found_items' => $scored]);
    }

    // ── Scoring (identical to MatchedItemController) ───────────────────────────

    private function scoreMatch(Item $report, Item $found): int
    {
        $score = 0;
        if ($report->item_type && $found->item_type && strcasecmp($report->item_type, $found->item_type) === 0) $score += 20;
        if ($report->color     && $found->color     && strcasecmp($report->color, $found->color) === 0)         $score += 20;
        if ($report->brand     && $found->brand     && strcasecmp($report->brand, $found->brand) === 0)         $score += 20;
        $score += (int) round($this->descSimilarity($report->item_description ?? '', $found->item_description ?? '') * 40);
        return $score;
    }

    private function descSimilarity(string $a, string $b): float
    {
        $clean = function (string $t): array {
            preg_match_all('/[a-z]{4,}/', strtolower(strip_tags($t)), $m);
            return array_unique($m[0]);
        };
        $w1 = $clean($a); $w2 = $clean($b);
        if (!$w1 || !$w2) return 0.0;
        return count(array_intersect($w1, $w2)) / count(array_unique(array_merge($w1, $w2)));
    }
}
