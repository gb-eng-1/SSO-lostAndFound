<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Notification;
use App\Support\FoundAtLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles student claim submissions.
 * Ported from STUDENT/Submitclaim.php.
 */
class ClaimController extends Controller
{
    /** @var list<string> */
    private const CATEGORIES = [
        'Electronics & Gadgets',
        'Document & Identification',
        'Personal Belongings',
        'Apparel & Accessories',
        'Miscellaneous',
    ];

    /** Shorter labels for table column (mockups). */
    private const CATEGORY_SHORT = [
        'Electronics & Gadgets' => 'Electronics',
        'Document & Identification' => 'Documents',
        'Personal Belongings' => 'Personal',
        'Apparel & Accessories' => 'Apparels',
        'Miscellaneous' => 'Miscellaneous',
    ];

    /**
     * POST /student/claim-intent — student confirms they will claim a matched item (no photo).
     * Enables the admin Matched Items "Claim" action after acknowledgement.
     */
    public function claimIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'found_item_id'  => 'required|string|max:50',
            'lost_report_id' => 'required|string|max:50',
        ]);

        $studentId    = session('student_id');
        $studentEmail = session('student_email');

        $foundItem = Item::foundItems()->find($validated['found_item_id']);
        if (! $foundItem) {
            return response()->json(['ok' => false, 'error' => 'Found item not found.'], 404);
        }

        if (! in_array($foundItem->status, ['For Verification', 'Matched', 'Unresolved Claimants', 'Unclaimed Items'], true)) {
            return response()->json(['ok' => false, 'error' => 'This item is not available for claiming.'], 422);
        }

        $lostReport = Item::lostReports()
            ->where('id', $validated['lost_report_id'])
            ->where('user_id', $studentEmail)
            ->first();

        if (! $lostReport) {
            return response()->json(['ok' => false, 'error' => 'Lost report not found or not yours.'], 403);
        }

        if ($lostReport->matched_barcode_id !== $foundItem->id) {
            return response()->json(['ok' => false, 'error' => 'This match is no longer linked.'], 422);
        }

        $existing = Claim::where('student_id', $studentId)
            ->where('found_item_id', $validated['found_item_id'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'ok'    => true,
                'reference_id' => null,
                'already_submitted' => true,
            ]);
        }

        $referenceId = Claim::generateReferenceId();

        Claim::create([
            'reference_id'      => $referenceId,
            'student_id'        => $studentId,
            'found_item_id'     => $validated['found_item_id'],
            'lost_report_id'    => $validated['lost_report_id'],
            'proof_photo'       => null,
            'proof_description' => 'Student acknowledged intent to claim via matched-item workflow. Visit the security office (lost and found).',
            'status'            => 'Pending',
        ]);

        return response()->json(['ok' => true, 'reference_id' => $referenceId]);
    }

    /** POST /student/claim */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'found_item_id'    => 'required|string|max:50',
            'lost_report_id'   => 'nullable|string|max:50',
            'proof_description' => 'nullable|string',
            'imageDataUrl'     => 'required|string',
        ]);

        $studentId    = session('student_id');
        $studentEmail = session('student_email');

        $foundItem = Item::foundItems()->find($validated['found_item_id']);
        if (!$foundItem) {
            return response()->json(['ok' => false, 'error' => 'Found item not found.'], 404);
        }

        // Prevent duplicate active claims
        $existing = Claim::where('student_id', $studentId)
            ->where('found_item_id', $validated['found_item_id'])
            ->whereIn('status', ['Pending', 'Approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'ok'    => false,
                'error' => 'You already have an active claim for this item.',
            ], 409);
        }

        // Validate lost_report belongs to this student
        if (!empty($validated['lost_report_id'])) {
            $lostReport = Item::lostReports()
                ->where('id', $validated['lost_report_id'])
                ->where('user_id', $studentEmail)
                ->first();

            if (!$lostReport) {
                return response()->json(['ok' => false, 'error' => 'Lost report not found or not yours.'], 403);
            }
        }

        $referenceId = Claim::generateReferenceId();

        $claim = Claim::create([
            'reference_id'     => $referenceId,
            'student_id'       => $studentId,
            'found_item_id'    => $validated['found_item_id'],
            'lost_report_id'   => $validated['lost_report_id'] ?? null,
            'proof_photo'      => $validated['imageDataUrl'],
            'proof_description' => $validated['proof_description'] ?? null,
            'status'           => 'Pending',
        ]);

        // Update item status to "Unresolved Claimants"
        $foundItem->update(['status' => 'Unresolved Claimants']);

        Notification::notifyAdmin(
            'claim_submitted',
            'New Claim Submitted',
            "Student submitted claim {$referenceId} for item {$validated['found_item_id']}.",
            $referenceId
        );

        return response()->json(['ok' => true, 'reference_id' => $referenceId]);
    }

    /**
     * GET /student/claim-detail?reference_id=
     * Same found-item shaping as Admin DashboardController::getItem for non-REF items.
     */
    public function detail(Request $request): JsonResponse
    {
        $referenceId = trim((string) $request->query('reference_id', ''));
        if ($referenceId === '') {
            return response()->json(['ok' => false, 'error' => 'Missing reference_id'], 400);
        }

        $studentId = session('student_id');
        $claim = Claim::where('reference_id', $referenceId)
            ->where('student_id', $studentId)
            ->with(['foundItem', 'lostReport'])
            ->first();

        if (! $claim) {
            return response()->json(['ok' => false, 'error' => 'Claim not found.'], 404);
        }

        $found = $claim->foundItem;
        if (! $found) {
            return response()->json(['ok' => false, 'error' => 'Found item missing.'], 404);
        }

        $itemPayload = $this->buildFoundItemDetail($found);
        $claimRecord = $found->parseClaimRecord();

        $uiClaimed = $found->status === 'Claimed';
        $claimant = null;
        if ($claimRecord) {
            $claimant = [
                'name'              => $claimRecord['name'] ?? null,
                'email'             => $claimRecord['email'] ?? null,
                'contact'           => $claimRecord['contact'] ?? null,
                'date_accomplished' => $claimRecord['date_accomplished'] ?? null,
            ];
        }

        $lost = $claim->lostReport;
        $lostSummary = null;
        if ($lost) {
            $meta = $lost->parseDescription();
            $lostSummary = [
                'display_ticket_id' => $lost->display_ticket_id,
                'department'        => $meta['Department'] ?? null,
                'student_number'    => $meta['Student Number'] ?? null,
                'contact'           => $meta['Contact'] ?? null,
                'date_lost'         => $lost->date_lost ? $lost->date_lost->format('Y-m-d') : null,
            ];
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'item'                => $itemPayload,
                'claim' => [
                    'reference_id'    => $claim->reference_id,
                    'status'          => $claim->status,
                    'claim_date'      => $claim->claim_date?->format('Y-m-d H:i:s'),
                    'resolution_date' => $claim->resolution_date?->format('Y-m-d H:i:s'),
                ],
                'lost_report_summary' => $lostSummary,
                'ui_status'           => $uiClaimed ? 'claimed' : 'pending',
                'claimant'            => $claimant,
            ],
        ]);
    }

    /**
     * Mirror Admin\DashboardController::getItem for found items (UB…).
     *
     * @return array<string, mixed>
     */
    private function buildFoundItemDetail(Item $item): array
    {
        $data = $item->toArray();
        $split = FoundAtLocation::split($item->found_at);
        $data['found_at_campus'] = $split['campus'];
        $data['found_at_detail'] = $split['found_in'];
        $parsedMeta = $item->parseDescription();
        $data['encoded_by_parsed'] = $parsedMeta['Encoded By'] ?? null;
        $data['image_data'] = null;

        return $data;
    }

    /**
     * GET /student/claim-history
     *
     * Claim History lists only student-submitted claims. "Pending" means the
     * student pressed Claim and is waiting for the office to complete the
     * process; merely being matched by the system does not create a Pending row.
     */
    public function history(Request $request)
    {
        $studentId = session('student_id');
        $categories = self::CATEGORIES;

        $claimStatus = (string) $request->query('claim_status', '');
        $claimCategoryIdx = (string) $request->query('claim_category', '');
        $legacyFilter = (string) $request->query('claim_filter', '');

        if ($legacyFilter !== '' && $claimStatus === '' && $claimCategoryIdx === '') {
            if ($legacyFilter === 'status_claimed') {
                $claimStatus = 'claimed';
            } elseif ($legacyFilter === 'status_pending') {
                $claimStatus = 'pending';
            } elseif (preg_match('/^cat_(\d+)$/', $legacyFilter, $m)) {
                $claimCategoryIdx = $m[1];
            }
        }

        $uiStatusFilter = null;
        $categoryFilter = null;

        if ($claimStatus === 'claimed') {
            $uiStatusFilter = 'Claimed';
        } elseif ($claimStatus === 'pending') {
            $uiStatusFilter = 'Pending';
        }

        if ($claimCategoryIdx !== '' && ctype_digit($claimCategoryIdx)) {
            $idx = (int) $claimCategoryIdx;
            if (isset($categories[$idx])) {
                $categoryFilter = $categories[$idx];
            }
        }

        $query = Claim::where('student_id', $studentId)
            ->with(['foundItem', 'lostReport']);

        if ($uiStatusFilter === 'Claimed') {
            $query->whereHas('foundItem', fn ($q) => $q->where('status', 'Claimed'));
        } elseif ($uiStatusFilter === 'Pending') {
            $query->where('status', 'Pending');
        }

        if ($categoryFilter) {
            $query->whereHas('foundItem', fn ($q) => $q->where('item_type', $categoryFilter));
        }

        $claims = $query->orderByDesc('created_at')->get();

        $tableRows = $claims->map(function (Claim $claim) {
            return array_merge($this->buildHistoryRow($claim), ['claim' => $claim]);
        });

        return view('student.claim-history', [
            'tableRows'        => $tableRows,
            'categories'       => $categories,
            'claimStatus'      => $claimStatus,
            'claimCategoryIdx' => $claimCategoryIdx,
        ]);
    }

    /**
     * @return array{
     *   reference_id: string,
     *   ticket_id: string,
     *   category: string,
     *   department: string,
     *   student_id: string,
     *   contact: string,
     *   date_lost: string,
     *   date_claimed: string,
     *   ui_status: string,
     *   status_class: string
     * }
     */
    private function buildHistoryRow(Claim $claim): array
    {
        $fi = $claim->foundItem;
        $lr = $claim->lostReport;

        $uiClaimed = $fi && $fi->status === 'Claimed';
        $claimRecord = $fi ? $fi->parseClaimRecord() : null;

        $type = $fi?->item_type ?? '';
        $category = (self::CATEGORY_SHORT[$type] ?? $type) ?: '—';

        $meta = $lr ? $lr->parseDescription() : [];
        $department = $meta['Department'] ?? '—';
        $studentNum = $meta['Student Number'] ?? '—';
        $contact = $meta['Contact'] ?? '—';

        $ticketId = $lr ? $lr->display_ticket_id : '—';

        $dateLost = ($lr && $lr->date_lost) ? $lr->date_lost->format('Y-m-d') : '—';

        if ($uiClaimed) {
            if ($claimRecord && ! empty($claimRecord['date_accomplished'])) {
                $raw = $claimRecord['date_accomplished'];
                $dateClaimed = strlen($raw) >= 10 ? substr($raw, 0, 10) : $raw;
            } elseif ($fi->updated_at) {
                $dateClaimed = $fi->updated_at->format('Y-m-d');
            } else {
                $dateClaimed = '—';
            }
        } else {
            $dateClaimed = $claim->claim_date ? $claim->claim_date->format('Y-m-d') : '—';
        }

        $uiStatus = $uiClaimed ? 'Claimed' : ($claim->status === 'Pending' ? 'Pending' : $claim->status);
        $statusClass = $uiClaimed ? 'ch-status-claimed' : 'ch-status-pending';

        return [
            'reference_id' => $claim->reference_id,
            'ticket_id'    => $ticketId,
            'category'     => $category,
            'department'   => $department,
            'student_id'   => $studentNum,
            'contact'      => $contact,
            'date_lost'    => $dateLost,
            'date_claimed' => $dateClaimed,
            'ui_status'    => $uiStatus,
            'status_class' => $statusClass,
        ];
    }
}
