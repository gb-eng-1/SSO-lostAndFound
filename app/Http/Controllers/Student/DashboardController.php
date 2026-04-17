<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Student;
use App\Support\StudentMatchPayload;
use Illuminate\Http\JsonResponse;

/**
 * Student dashboard — my reports summary, recently matched items, claim action.
 * Ported from STUDENT/StudentDashboard.php.
 */
class DashboardController extends Controller
{
    /** GET /student */
    public function index()
    {
        $studentEmail = session('student_email');
        $studentId    = session('student_id');
        $studentName  = session('student_name');

        $myReports = $this->loadMyReports($studentEmail, $studentId);

        $matchedPairs = StudentMatchPayload::matchedPairsForStudent($studentEmail, $studentId);

        $recentActivity = Notification::forStudent((int) $studentId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $matchedPairsPayload = StudentMatchPayload::fromPairs($matchedPairs);

        return view('student.dashboard', compact(
            'studentName',
            'myReports',
            'matchedPairs',
            'matchedPairsPayload',
            'recentActivity'
        ));
    }

    /** GET /student/dashboard/summary — lightweight JSON for client-side polling. */
    public function summary(): JsonResponse
    {
        $studentEmail = session('student_email');
        $studentId    = session('student_id');

        $myReports = $this->loadMyReports($studentEmail, $studentId);
        $matchedPairsPayload = StudentMatchPayload::fromPairs(
            StudentMatchPayload::matchedPairsForStudent($studentEmail, $studentId)
        );

        $myReportsPreview = $myReports
            ->filter(fn ($r) => ! $r->matched_barcode_id || ! in_array($r->status, ['For Verification', 'Matched', 'Unresolved Claimants']))
            ->take(5)
            ->map(fn (Item $r) => [
                'id'                => $r->id,
                'display_ticket_id' => $r->display_ticket_id,
                'item_type'         => $r->item_type ?? '—',
                'date_lost'         => $r->date_lost ? $r->date_lost->format('Y-m-d') : '—',
            ])
            ->values();

        return response()->json([
            'ok'                    => true,
            'matched_pairs_payload' => $matchedPairsPayload,
            'my_reports_preview'    => $myReportsPreview,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Item>
     */
    private function loadMyReports(?string $studentEmail, $studentId)
    {
        $studentNumber = Student::find($studentId)?->student_id;
        $userIds = array_values(array_filter([$studentEmail, $studentNumber ? $studentNumber . '@ub.edu.ph' : null]));

        return Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->where('status', '!=', 'Cancelled')
            ->orderByDesc('date_lost')
            ->get()
            ->map(function (Item $report) {
                if ($report->matched_barcode_id) {
                    $report->matched_found_item = Item::find($report->matched_barcode_id);
                }
                $report->active_claim = Claim::where('lost_report_id', $report->id)
                    ->whereIn('status', ['Pending', 'Approved'])
                    ->first();

                return $report;
            });
    }

}
