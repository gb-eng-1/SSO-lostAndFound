<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Student;
use App\Services\AutoMatchService;
use App\Services\ItemPurgeService;
use App\Services\MatchScoringService;
use App\Support\ReportColors;
use App\Support\StudentMatchPayload;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Manages student lost report creation, listing, and cancellation.
 * Ported from save_lost_report.php, STUDENT/StudentsReport.php, and STUDENT/CancelReport.php.
 */
class LostReportController extends Controller
{
    /** @var list<string> */
    private const CATEGORIES = [
        'Electronics & Gadgets',
        'Document & Identification',
        'Personal Belongings',
        'Apparel & Accessories',
        'Miscellaneous',
    ];

    /** Must match options in `partials/lost-report-form-fields` document type dropdown. */
    public const DOCUMENT_TYPES = [
        'Student ID',
        'Driver\'s License',
        'Passport',
        'Person\'s With Disability (PWD) ID',
        'Voter\'s ID',
        'Company/Employee ID',
        'National ID',
        'Senior Citizen ID',
    ];

    /** GET /student/reports */
    public function index(Request $request)
    {
        $filter       = $request->query('filter', 'all');
        $categoryFilter = $request->query('category');
        $studentEmail = session('student_email');
        $studentId    = session('student_id');

        // Match by email AND by student_number@ub.edu.ph format (same as original PHP)
        $studentNumber = Student::find($studentId)?->student_id;
        $userIds = array_values(array_filter([$studentEmail, $studentNumber ? $studentNumber . '@ub.edu.ph' : null]));

        $baseQuery = Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->where('status', '!=', 'Cancelled')
            ->when($categoryFilter, fn ($q) => $q->where('item_type', $categoryFilter));

        if ($filter === 'matched') {
            $reports = $baseQuery->whereNotNull('matched_barcode_id')
                ->whereIn('status', ['For Verification', 'Matched', 'Unresolved Claimants'])
                ->orderByDesc('date_lost')
                ->get()
                ->map(function (Item $r) {
                    $r->matched_found_item = $r->matched_barcode_id
                        ? Item::find($r->matched_barcode_id)
                        : null;
                    return $r;
                });
        } else {
            $reports = $baseQuery
                ->where(function ($q) {
                    $q->whereNull('matched_barcode_id')
                      ->orWhereNotIn('status', ['For Verification', 'Matched', 'Unresolved Claimants']);
                })
                ->orderByDesc('date_lost')
                ->get()
                ->map(function (Item $r) {
                    $meta = $r->parseDescription();
                    $r->parsed_department     = $meta['Department'] ?? null;
                    $r->parsed_student_number = $meta['Student Number'] ?? null;
                    $r->parsed_contact        = $meta['Contact'] ?? null;
                    $r->active_claim = Claim::where('lost_report_id', $r->id)
                        ->whereIn('status', ['Pending', 'Approved'])
                        ->first();
                    if ($r->matched_barcode_id) {
                        $r->matched_found_item = Item::find($r->matched_barcode_id);
                    }

                    return $r;
                });
        }

        $matchedPairsColl = $reports->filter(function (Item $r) {
            return $r->matched_barcode_id && $r->matched_found_item;
        })->map(function (Item $r) {
            return [
                'lost_report'  => $r,
                'found_item'   => $r->matched_found_item,
            ];
        })->values();

        $matchedPairsPayload = StudentMatchPayload::fromPairs($matchedPairsColl);

        $categories = self::CATEGORIES;

        return view('student.reports', compact(
            'reports',
            'filter',
            'matchedPairsPayload',
            'categoryFilter',
            'categories'
        ));
    }

    /**
     * Create a new lost report.
     * Ported from save_lost_report.php.
     * POST /student/reports (accepts JSON body to match existing JS fetch calls)
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'category'         => 'nullable|string|max:100',
            'full_name'        => 'nullable|string|max:200',
            'contact_number'   => 'required|string|max:50',
            'department'       => 'required|string|max:200',
            'id'               => 'nullable|string|max:100',   // student ID number
            'item'             => 'nullable|string|max:200',
            'item_description' => 'required|string',
            'color'            => ['nullable', 'string', 'max:100', Rule::in(array_merge([''], ReportColors::ALLOWED))],
            'brand'            => 'nullable|string|max:100',
            'date_lost'        => 'nullable|date|before_or_equal:today',
            'imageDataUrl'     => 'nullable|string',
            'student_email'    => 'nullable|email',
            'document_type'    => 'nullable|string|max:120',
        ];
        if ($request->input('category') === 'Document & Identification') {
            $rules['document_type'] = ['required', Rule::in(self::DOCUMENT_TYPES)];
        }

        $validated = $request->validate($rules);

        // Determine the user_id to store
        $studentEmail = session('student_email')
            ?? ($validated['student_email'] ?? null);

        $studentNumber  = trim($validated['id'] ?? '');
        $userId = $studentEmail
            ?? ($studentNumber ? $studentNumber . '@ub.edu.ph' : ($validated['full_name'] ?? null));

        // Pack metadata into item_description (matches existing parser)
        $desc   = trim($validated['item_description']);
        if (($validated['category'] ?? '') === 'Document & Identification' && ! empty($validated['document_type'])) {
            $desc = 'Document Type: ' . $validated['document_type'] . "\n" . $desc;
        }
        $prepend = [];
        $fullName = trim($validated['full_name'] ?? '');
        if ($fullName === '' && session('student_id')) {
            $fullName = trim((string) Student::find(session('student_id'))?->name);
        }
        if ($fullName !== '') {
            $prepend[] = 'Full Name: ' . $fullName;
        }
        if ($studentNumber) {
            $prepend[] = 'Student Number: ' . $studentNumber;
        }
        if (!empty($validated['item'])) {
            $prepend[] = 'Item Type: ' . $validated['item'];
        }
        if ($prepend) {
            $desc = implode("\n", $prepend) . "\n" . $desc;
        }
        $contact = trim($validated['contact_number'] ?? '');
        $dept    = trim($validated['department'] ?? '');
        if ($contact || $dept) {
            $desc .= ($desc ? "\n" : '') . 'Contact: ' . $contact;
            if ($dept) {
                $desc .= "\nDepartment: " . $dept;
            }
        }

        $refId = Item::generateRefId();

        Item::create([
            'id'               => $refId,
            'user_id'          => $userId,
            'item_type'        => $validated['category'] ?? null,
            'color'            => $validated['color'] ?? null,
            'brand'            => $validated['brand'] ?? null,
            'date_encoded'     => now()->toDateString(),
            'date_lost'        => $validated['date_lost'] ?? null,
            'item_description' => $desc,
            'image_data'       => $validated['imageDataUrl'] ?? null,
            'status'           => 'Unclaimed Items',
        ]);

        Notification::notifyAdmin(
            'lost_report_submitted',
            'New Lost Report',
            "Student submitted lost report {$refId}.",
            $refId
        );

        ActivityLog::record('lost_report', $refId, "New report by {$userId}", session('student_id'), 'student');

        // Trigger auto-matching immediately for this new report
        $newItem = Item::find($refId);
        if ($newItem) {
            (new AutoMatchService(new MatchScoringService()))->runForReport($newItem);
        }

        return response()->json(['ok' => true, 'id' => $refId]);
    }

    /** POST /student/reports/{id}/cancel */
    public function cancel(string $id): JsonResponse
    {
        $studentEmail  = session('student_email');
        $studentId     = session('student_id');
        $studentNumber = Student::find($studentId)?->student_id;
        $userIds = array_values(array_filter([$studentEmail, $studentNumber ? $studentNumber . '@ub.edu.ph' : null]));

        $report = Item::lostReports()
            ->where('id', $id)
            ->whereIn('user_id', $userIds)
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled'])
            ->firstOrFail();

        // Cooldown is based on when the report was submitted (created_at), not date lost.
        if ($report->created_at && Carbon::now()->lt(Carbon::parse($report->created_at)->addHours(24))) {
            return response()->json([
                'ok' => false,
                'error' => 'You can cancel this report 24 hours after it was submitted.',
            ], 422);
        }

        (new ItemPurgeService)->purge($id);

        return response()->json(['ok' => true]);
    }

}
