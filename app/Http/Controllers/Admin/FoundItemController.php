<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\LostReportController;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Notification;
use App\Services\AdminInternalEncodeService;
use App\Services\AutoMatchService;
use App\Services\ItemPurgeService;
use App\Services\MatchScoringService;
use App\Support\FoundAtLocation;
use App\Support\ReportColors;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

/**
 * Manages the Found Items page (Internal + Guest tabs).
 * Ported from ADMIN/FoundAdmin.php, save_encoded_item.php, and save_guest_item.php.
 */
class FoundItemController extends Controller
{
    private const INTERNAL_RETENTION_YEARS = 2;
    private const GUEST_RETENTION_YEARS    = 1;

    private const CATEGORIES = [
        'Electronics & Gadgets',
        'Document & Identification',
        'Personal Belongings',
        'Apparel & Accessories',
        'Miscellaneous',
        'ID & Nameplate',
    ];

    /** Categories shown in the internal table / filter (excludes guest ID type). */
    private const INTERNAL_CATEGORY_OPTIONS = [
        'Electronics & Gadgets',
        'Document & Identification',
        'Personal Belongings',
        'Apparel & Accessories',
        'Miscellaneous',
    ];

    private const STATUSES = [
        'Unclaimed Items',
        'For Verification',
        'Unresolved Claimants',
        'Matched',
        'Claimed',
        'Resolved',
        'Cancelled',
        'Disposed',
    ];

    /** GET /admin/found-items */
    public function index(Request $request)
    {
        $category = $request->query('category');
        $status   = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $dateRange = $this->resolveDateRange($request->query('date_filter'), $dateFrom, $dateTo);

        // Internal found items (not REF-, not guest ID type); hide Cancelled/Disposed unless status filter applied
        $internalQuery = Item::foundItems()
            ->where('item_type', '!=', 'ID & Nameplate')
            ->when(!$status, fn($q) => $q->whereNotIn('status', ['Cancelled', 'Disposed']))
            ->when($category && $category !== 'ID & Nameplate', fn($q) => $q->where('item_type', $category))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($dateRange['from'], fn($q) => $q->where('date_encoded', '>=', $dateRange['from']))
            ->when($dateRange['to'], fn($q) => $q->where('date_encoded', '<=', $dateRange['to']))
            ->orderByDesc('date_encoded');

        $internalItems = $internalQuery->get()
            ->map(fn($item) => $this->attachRetention($item, self::INTERNAL_RETENTION_YEARS));

        // Guest items (ID & Nameplate type) — category filter: only show when "All" or "ID & Nameplate"
        $guestQuery = Item::foundItems()
            ->where('item_type', 'ID & Nameplate')
            ->when(!$status, fn($q) => $q->whereNotIn('status', ['Cancelled', 'Disposed']))
            ->when($category && $category !== 'ID & Nameplate', fn($q) => $q->whereRaw('1=0'))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($dateRange['from'], fn($q) => $q->where('date_encoded', '>=', $dateRange['from']))
            ->when($dateRange['to'], fn($q) => $q->where('date_encoded', '<=', $dateRange['to']))
            ->orderByDesc('date_encoded');

        $guestItems = $guestQuery->get()
            ->map(fn($item) => $this->attachRetention($item, self::GUEST_RETENTION_YEARS));

        // Items expiring within 30 days (for View more modal)
        $expiringItems = collect()
            ->merge($internalItems->where('expires_in_30_days', true))
            ->merge($guestItems->where('expires_in_30_days', true))
            ->values();

        $overdueCount = $internalItems->where('is_overdue', true)->count()
            + $guestItems->where('is_overdue', true)->count();

        return view('admin.found-items', [
            'internalItems'      => $internalItems,
            'guestItems'         => $guestItems,
            'expiringItems'      => $expiringItems,
            'overdueCount'       => $overdueCount,
            'categories'         => self::CATEGORIES,
            'categoriesInternal' => self::INTERNAL_CATEGORY_OPTIONS,
            'campusLocations'    => FoundAtLocation::campusOptions(),
            'statuses'           => self::STATUSES,
        ]);
    }

    private function resolveDateRange(?string $filter, ?string $from, ?string $to): array
    {
        if ($from || $to) {
            return ['from' => $from, 'to' => $to ?: Carbon::now()->toDateString()];
        }
        $now = Carbon::now();
        return match ($filter) {
            'today'     => ['from' => $now->toDateString(), 'to' => $now->toDateString()],
            'week'      => ['from' => $now->copy()->startOfWeek()->toDateString(), 'to' => $now->toDateString()],
            'month'     => ['from' => $now->copy()->startOfMonth()->toDateString(), 'to' => $now->toDateString()],
            '3months'   => ['from' => $now->copy()->subMonths(3)->toDateString(), 'to' => $now->toDateString()],
            'year'      => ['from' => $now->copy()->startOfYear()->toDateString(), 'to' => $now->toDateString()],
            default     => ['from' => null, 'to' => null],
        };
    }

    /**
     * GET /admin/found-items/barcode-context?barcode=UB…
     * Preflight for encode forms: whether a found-item id exists and how many lost reports are linked.
     */
    public function barcodeContext(Request $request): JsonResponse
    {
        $barcode = trim((string) $request->query('barcode', ''));
        if ($barcode === '') {
            return response()->json(['ok' => false, 'error' => 'Missing barcode parameter.'], 422);
        }
        if (str_starts_with($barcode, 'REF-')) {
            return response()->json(['ok' => false, 'error' => 'Invalid barcode for a found item.'], 422);
        }

        $exists = Item::foundItems()->where('id', $barcode)->exists();
        $linkedCount = $exists ? $this->linkedLostReportCountForFoundItem($barcode) : 0;

        return response()->json([
            'ok'                    => true,
            'exists'                => $exists,
            'linked_report_count'   => $linkedCount,
        ]);
    }

    /** POST /admin/found-items — encode a new internal found item */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_id'       => 'required|string|max:50',
            'category'         => 'nullable|string|max:100',
            'item'             => 'required|string|max:200',
            'color'            => 'required|string|max:100',
            'brand'            => 'nullable|string|max:100',
            'item_description' => 'required|string',
            'storage_location' => 'nullable|string|max:200',
            'found_at'         => 'nullable|string|max:100',
            'found_in'         => 'nullable|string|max:160',
            'found_by'         => 'nullable|string|max:200',
            'date_found'       => 'nullable|date|before_or_equal:today',
            'imageDataUrl'     => 'nullable|string',
        ]);

        try {
            $result = app(AdminInternalEncodeService::class)->createFromValidated($validated);
            $item = $result['item'];

            return response()->json([
                'ok' => true,
                'id' => $item->id,
                'auto_match' => [
                    'linked' => $result['linked_report_id'] !== null,
                    'lost_report_id' => $result['linked_report_id'],
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 409);
        }
    }

    /**
     * Admin creates a lost report on behalf of a student (Encode Report).
     * POST /admin/found-items/lost-report
     */
    public function storeLostReport(Request $request): JsonResponse
    {
        $rules = [
            'category'         => 'nullable|string|max:100',
            'full_name'        => 'nullable|string|max:200',
            'contact_number'   => 'required|string|max:50',
            'department'       => 'required|string|max:200',
            'id'               => 'nullable|string|max:100',
            'item'             => 'nullable|string|max:200',
            'item_description' => 'required|string',
            'color'            => ['nullable', 'string', 'max:100', Rule::in(array_merge([''], ReportColors::ALLOWED))],
            'brand'            => 'nullable|string|max:100',
            'date_lost'        => 'nullable|date|before_or_equal:today',
            'imageDataUrl'     => 'nullable|string',
            'student_email'    => 'required|email',
            'document_type'    => 'nullable|string|max:120',
        ];
        if ($request->input('category') === 'Document & Identification') {
            $rules['document_type'] = ['required', Rule::in(LostReportController::DOCUMENT_TYPES)];
        }

        $validated = $request->validate($rules);

        $userId = trim($validated['student_email']);

        $desc   = trim($validated['item_description']);
        if (($validated['category'] ?? '') === 'Document & Identification' && ! empty($validated['document_type'])) {
            $desc = 'Document Type: ' . $validated['document_type'] . "\n" . $desc;
        }
        $prepend = [];
        if (!empty($validated['full_name'])) {
            $prepend[] = 'Full Name: ' . trim($validated['full_name']);
        }
        $studentNumber = trim($validated['id'] ?? '');
        if ($studentNumber !== '') {
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
            $desc .= ($desc !== '' ? "\n" : '') . 'Contact: ' . $contact;
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
            "Admin submitted lost report {$refId} for {$userId}.",
            $refId
        );

        ActivityLog::record(
            'lost_report',
            $refId,
            "New report for {$userId} (admin)",
            session('admin_id'),
            'admin'
        );

        $newItem = Item::find($refId);
        if ($newItem) {
            (new AutoMatchService(new MatchScoringService()))->runForReport($newItem);
        }

        return response()->json(['ok' => true, 'id' => $refId]);
    }

    /** POST /admin/found-items/guest — encode a guest-surrendered ID/document (legacy; route uses GuestItemController) */
    public function storeGuest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_id'       => 'required|string|max:50',
            'id_type'          => 'nullable|string|max:100',
            'fullname'         => 'required|string|max:200',
            'color'            => ['required', 'string', 'max:100', Rule::in(ReportColors::ALLOWED)],
            'storage_location' => 'nullable|string|max:200',
            'encoded_by'       => 'nullable|string|max:200',
            'found_by'         => 'nullable|string|max:200',
            'date_surrendered' => 'nullable|date|before_or_equal:today',
            'imageDataUrl'     => 'nullable|string',
        ]);

        $barcodeId = trim($validated['barcode_id']);
        if ($barcodeId === '') {
            return response()->json(['ok' => false, 'error' => 'Barcode ID is required.'], 422);
        }
        if (str_starts_with($barcodeId, 'REF-')) {
            return response()->json(['ok' => false, 'error' => 'Invalid Barcode ID.'], 422);
        }

        if (Item::find($barcodeId)) {
            return response()->json(['ok' => false, 'error' => "Barcode '{$barcodeId}' already exists."], 409);
        }

        $desc = '';
        if (!empty($validated['found_by'])) {
            $desc = 'Found By: ' . trim($validated['found_by']) . "\n";
        }
        $desc .= 'Owner: ' . $validated['fullname'] . "\nID Type: " . ($validated['id_type'] ?? '');

        $item = Item::create([
            'id'               => $barcodeId,
            'item_type'        => 'ID & Nameplate',
            'item_description' => $desc,
            'color'            => $validated['color'],
            'storage_location' => $validated['storage_location'] ?? null,
            'found_by'         => $validated['encoded_by'] ?? null,
            'date_encoded'     => $validated['date_surrendered'] ?? now()->toDateString(),
            'image_data'       => $validated['imageDataUrl'] ?? null,
            'status'           => 'Unclaimed Items',
        ]);

        ActivityLog::record('encoded', $barcodeId, "Encoded guest item {$barcodeId}", session('admin_id'), 'admin');

        return response()->json(['ok' => true, 'id' => $barcodeId]);
    }

    /** DELETE /admin/found-items/{id} — permanently remove item and links */
    public function destroy(string $id): JsonResponse
    {
        try {
            Item::findOrFail($id);
            (new ItemPurgeService)->purge($id);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok'    => false,
                'error' => 'Could not remove item.',
            ], 500);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function linkedLostReportCountForFoundItem(string $foundItemId): int
    {
        $ids = Item::lostReports()
            ->where('matched_barcode_id', $foundItemId)
            ->pluck('id');

        if (Schema::hasTable('item_matches')) {
            $fromMatches = DB::table('item_matches')
                ->where('found_item_id', $foundItemId)
                ->pluck('lost_report_id');
            $ids = $ids->merge($fromMatches);
        }

        return $ids->unique()->values()->count();
    }

    private function attachRetention(Item $item, int $years): Item
    {
        $base = $item->date_encoded ?? $item->created_at;
        if ($base) {
            $retentionEnd = Carbon::parse($base)->addYears($years);
            $item->retention_end      = $retentionEnd->toDateString();
            $item->is_overdue         = $retentionEnd->isPast();
            $item->expires_in_30_days = !$item->is_overdue && $retentionEnd->isFuture() && now()->diffInDays($retentionEnd, false) <= 30;
        }
        return $item;
    }
}
