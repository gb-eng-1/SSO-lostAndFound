<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AdminInternalEncodeService;
use App\Support\FoundAtLocation;
use App\Support\ReportColors;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin Dashboard — statistics, charts, summary tables, and AJAX encode/link actions.
 * Ported from ADMIN/AdminDashboard.php.
 */
class DashboardController extends Controller
{
    /**
     * Found items listed under Recovered Items (internal) — excludes guest-tab bucket.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Item>
     */
    private function foundItemsInternalNonGuest()
    {
        return Item::foundItems()->where(function ($q) {
            $q->whereNull('item_type')
                ->orWhereNotIn('item_type', ['ID & Nameplate', 'Document & Identification']);
        });
    }

    // ── Shared count helpers (used by index + summary polling) ────────────

    /** Internal found items excluding Cancelled/Disposed — matches Found Items internal tab default. */
    private function countInternalRecovered(): int
    {
        return $this->foundItemsInternalNonGuest()
            ->whereNotIn('status', ['Cancelled', 'Disposed'])
            ->count();
    }

    /** Recovered IDs tab bucket (ID & Nameplate + Document & Identification encodes). */
    private function countExternalIds(): int
    {
        return Item::foundItems()
            ->whereIn('item_type', ['ID & Nameplate', 'Document & Identification'])
            ->whereNotIn('status', ['Cancelled', 'Disposed'])
            ->count();
    }

    /** Active lost reports excluding terminal statuses — matches Reports page default. */
    private function countUnresolved(): int
    {
        return Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->count();
    }

    /** Found items awaiting verification — matches Matched Items page. */
    private function countForVerification(): int
    {
        return Item::foundItems()
            ->where('status', 'For Verification')
            ->count();
    }

    /** GET /admin */
    public function index()
    {
        $internalRecovered = $this->countInternalRecovered();
        $externalIds       = $this->countExternalIds();
        $unresolved        = $this->countUnresolved();
        $forVerification   = $this->countForVerification();

        $charts = $this->buildStatusDistributionCharts($internalRecovered, $externalIds, $unresolved, $forVerification);
        $pieData = $charts['pieData'];
        $pieCaption = $charts['pieCaption'];
        $barData = $charts['barData'];
        $barCaption = $charts['barCaption'];

        $recoveredInternal = $this->queryRecoveredInternalTable();
        $recoveredExternal = $this->queryRecoveredExternalTable();
        $unresolvedItems   = $this->queryUnresolvedClaimantsTable();
        $verificationItems = $this->queryVerificationTable();
        $recentActivity    = $this->buildRecentActivityList();

        $campusLocations = FoundAtLocation::campusOptions();

        return view('admin.dashboard', compact(
            'internalRecovered',
            'externalIds',
            'unresolved',
            'forVerification',
            'pieData',
            'pieCaption',
            'barData',
            'barCaption',
            'recoveredInternal',
            'recoveredExternal',
            'unresolvedItems',
            'verificationItems',
            'recentActivity',
            'campusLocations'
        ));
    }

    /**
     * GET /admin/dashboard/summary — JSON for dashboard cards + charts (optional polling).
     */
    public function summary(): JsonResponse
    {
        $internalRecovered = $this->countInternalRecovered();
        $externalIds       = $this->countExternalIds();
        $unresolved        = $this->countUnresolved();
        $forVerification   = $this->countForVerification();

        $charts = $this->buildStatusDistributionCharts($internalRecovered, $externalIds, $unresolved, $forVerification);
        $pieData = $charts['pieData'];
        $pieCaption = $charts['pieCaption'];
        $barData = $charts['barData'];
        $barCaption = $charts['barCaption'];

        $tables = $this->serializeDashboardTablesForJson(
            $this->queryRecoveredInternalTable(),
            $this->queryRecoveredExternalTable(),
            $this->queryUnresolvedClaimantsTable(),
            $this->queryVerificationTable()
        );
        $recentActivityJson = $this->serializeRecentActivityForJson($this->buildRecentActivityList());

        return response()->json([
            'ok' => true,
            'internal_recovered' => $internalRecovered,
            'external_ids'         => $externalIds,
            'unresolved'           => $unresolved,
            'for_verification'     => $forVerification,
            'pie_data'             => $pieData,
            'pie_caption'          => $pieCaption,
            'bar_data'             => $barData,
            'bar_caption'          => $barCaption,
            'recovered_internal'   => $tables['recovered_internal'],
            'recovered_external'   => $tables['recovered_external'],
            'unresolved_items'     => $tables['unresolved_items'],
            'verification_items'   => $tables['verification_items'],
            'recent_activity'      => $recentActivityJson,
        ]);
    }

    /**
     * Pie and bar charts share the same four status buckets with raw counts and percentages.
     *
     * @return array{pieData: list<array{label: string, count: int, pct: float, color: string}>, pieCaption: string, barData: list<array{label: string, count: int, pct: float, color: string}>, barCaption: string}
     */
    private function buildStatusDistributionCharts(
        int $internalRecovered,
        int $externalIds,
        int $unresolved,
        int $forVerification
    ): array {
        $total = $internalRecovered + $externalIds + $unresolved + $forVerification;

        if ($total <= 0) {
            return [
                'pieData' => [],
                'pieCaption' => 'No data available.',
                'barData' => [],
                'barCaption' => 'No data available.',
            ];
        }

        $buckets = [
            ['label' => 'Recovered Items', 'count' => $internalRecovered, 'color' => '#F57C00'],
            ['label' => 'Recovered IDs (External)', 'count' => $externalIds, 'color' => '#9C27B0'],
            ['label' => 'Unresolved Claimants', 'count' => $unresolved, 'color' => '#E55C5C'],
            ['label' => 'For Verification', 'count' => $forVerification, 'color' => '#8BC34A'],
        ];

        $pieData = [];
        $runningPct = 0;
        foreach ($buckets as $i => $b) {
            $pct = ($i === count($buckets) - 1)
                ? round(100 - $runningPct, 1)
                : round($b['count'] / $total * 100, 1);
            $runningPct += $pct;
            $pieData[] = [
                'label' => $b['label'],
                'count' => $b['count'],
                'pct'   => $pct,
                'color' => $b['color'],
            ];
        }

        $top = collect($pieData)->sortByDesc('count')->first();
        $caption = ($top['label'] ?? '') . ' has the highest count (' . ($top['count'] ?? 0) . ')';

        return [
            'pieData' => $pieData,
            'pieCaption' => $caption,
            'barData' => $pieData,
            'barCaption' => $caption,
        ];
    }

    /** @return \Illuminate\Support\Collection<int, Item> */
    private function queryRecoveredInternalTable()
    {
        return $this->foundItemsInternalNonGuest()
            ->whereNotIn('status', ['Cancelled', 'Disposed'])
            ->orderByDesc('date_encoded')
            ->limit(7)
            ->get()
            ->map(function (Item $item) {
                $end = $item->retentionEndDate();
                $item->retention_end = $end ? $end->format('Y-m-d') : 'N/A';

                return $item;
            });
    }

    /** @return \Illuminate\Support\Collection<int, Item> */
    private function queryRecoveredExternalTable()
    {
        return Item::foundItems()
            ->whereIn('item_type', ['ID & Nameplate', 'Document & Identification'])
            ->whereNotIn('status', ['Cancelled', 'Disposed'])
            ->orderByDesc('date_encoded')
            ->limit(7)
            ->get()
            ->map(function (Item $item) {
                $end = $item->retentionEndDate();
                $item->retention_end = $end ? $end->format('Y-m-d') : 'N/A';

                return $item;
            });
    }

    /** @return \Illuminate\Support\Collection<int, Item> */
    private function queryUnresolvedClaimantsTable()
    {
        return Item::lostReports()
            ->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed'])
            ->orderByDesc('date_lost')
            ->limit(7)
            ->get()
            ->map(function (Item $item) {
                $meta = $item->parseDescription();
                $item->parsed_department = $meta['Department'] ?? '—';
                $item->parsed_student_number = $meta['Student Number'] ?? '—';
                $item->parsed_contact = $meta['Contact'] ?? '—';

                return $item;
            });
    }

    /** @return \Illuminate\Support\Collection<int, Item> */
    private function queryVerificationTable()
    {
        return Item::foundItems()
            ->where('status', 'For Verification')
            ->orderByDesc('date_encoded')
            ->limit(5)
            ->get()
            ->map(function (Item $item) {
                $end = $item->retentionEndDate();
                $item->retention_end = $end ? $end->format('Y-m-d') : 'N/A';

                return $item;
            });
    }

    /**
     * @return array<int, array{action:string, item_id:string, item_name:string, created_at:mixed}>
     */
    private function buildRecentActivityList(): array
    {
        $activities = [];

        $matchedItems = Item::foundItems()
            ->whereIn('id', function ($sub) {
                $sub->select('matched_barcode_id')
                    ->from('items')
                    ->where('id', 'LIKE', 'REF-%')
                    ->whereNotNull('matched_barcode_id')
                    ->where('matched_barcode_id', '!=', '');
            })
            ->orderByDesc('created_at')
            ->limit(4)
            ->get(['id', 'item_type', 'color', 'brand', 'created_at']);

        foreach ($matchedItems as $item) {
            $activities[] = [
                'action' => 'matched',
                'item_id' => $item->id,
                'item_name' => trim($item->item_type ?? ''),
                'created_at' => $item->created_at,
            ];
        }

        $lostItems = Item::lostReports()
            ->orderByDesc('created_at')
            ->limit(4)
            ->get(['id', 'item_type', 'color', 'brand', 'created_at']);

        foreach ($lostItems as $item) {
            $activities[] = [
                'action' => 'lost',
                'item_id' => $item->id,
                'item_name' => trim($item->item_type ?? ''),
                'created_at' => $item->created_at,
            ];
        }

        $foundItems = Item::foundItems()
            ->whereNotIn('id', $matchedItems->pluck('id')->toArray())
            ->orderByDesc('created_at')
            ->limit(4)
            ->get(['id', 'item_type', 'color', 'brand', 'created_at']);

        foreach ($foundItems as $item) {
            $activities[] = [
                'action' => 'found',
                'item_id' => $item->id,
                'item_name' => trim($item->item_type ?? ''),
                'created_at' => $item->created_at,
            ];
        }

        usort($activities, fn ($a, $b) => strcmp(
            (string) ($b['created_at'] ?? ''),
            (string) ($a['created_at'] ?? '')
        ));

        return array_slice($activities, 0, 8);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Item>  $recoveredInternal
     * @param  \Illuminate\Support\Collection<int, Item>  $recoveredExternal
     * @param  \Illuminate\Support\Collection<int, Item>  $unresolvedItems
     * @param  \Illuminate\Support\Collection<int, Item>  $verificationItems
     * @return array{recovered_internal: array, recovered_external: array, unresolved_items: array, verification_items: array}
     */
    private function serializeDashboardTablesForJson($recoveredInternal, $recoveredExternal, $unresolvedItems, $verificationItems): array
    {
        return [
            'recovered_internal' => $recoveredInternal->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'item_type' => $item->item_type ?? '—',
                    'found_at' => $item->found_at ?? '—',
                    'date_encoded' => $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—',
                    'retention_end' => $item->retention_end ?? 'N/A',
                ];
            })->values()->all(),
            'recovered_external' => $recoveredExternal->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'found_by' => $item->found_by ?? '—',
                    'storage_location' => $item->storage_location ?? '—',
                    'date_encoded' => $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—',
                    'retention_end' => $item->retention_end ?? 'N/A',
                ];
            })->values()->all(),
            'unresolved_items' => $unresolvedItems->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'display_ticket_id' => $item->display_ticket_id,
                    'item_type' => $item->item_type ?? '—',
                    'parsed_department' => $item->parsed_department ?? '—',
                    'parsed_student_number' => $item->parsed_student_number ?? '—',
                    'parsed_contact' => $item->parsed_contact ?? '—',
                ];
            })->values()->all(),
            'verification_items' => $verificationItems->map(function (Item $item) {
                return [
                    'id' => $item->id,
                    'item_type' => $item->item_type ?? '—',
                    'found_at' => $item->found_at ?? '—',
                    'retention_end' => $item->retention_end ?? 'N/A',
                    'storage_location' => $item->storage_location ?? '—',
                    'date_encoded' => $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—',
                ];
            })->values()->all(),
        ];
    }

    /**
     * @param  array<int, array{action:string, item_id:string, item_name:string, created_at:mixed}>  $recentActivity
     * @return array<int, array{action:string, item_id:string, item_name:string, created_at:string|null}>
     */
    private function serializeRecentActivityForJson(array $recentActivity): array
    {
        return array_map(function (array $act) {
            $dt = $act['created_at'] ?? null;
            $createdAtIso = null;
            if ($dt) {
                $createdAtIso = Carbon::parse($dt)->toIso8601String();
            }

            return [
                'action' => $act['action'] ?? 'found',
                'item_id' => $act['item_id'] ?? '',
                'item_name' => $act['item_name'] ?? '',
                'created_at' => $createdAtIso,
            ];
        }, $recentActivity);
    }


    /**
     * GET /admin/item?id=xxx — AJAX item lookup.
     *
     * `data.view_preset`: `lost_report` (REF-…), `found_internal` (UB… non–guest), `found_external` (ID & Nameplate).
     * Optional `data.linked_summary`: `matched_found_item_id` or `matched_lost_report_id`.
     */
    public function getItem(Request $request): JsonResponse
    {
        $id = trim($request->query('id', ''));
        if (!$id) {
            return response()->json(['ok' => false, 'error' => 'Missing id'], 400);
        }
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['ok' => false, 'error' => 'Item not found'], 404);
        }
        $data = $item->toArray();
        $split = FoundAtLocation::split($item->found_at);
        $data['found_at_campus'] = $split['campus'];
        $data['found_at_detail'] = $split['found_in'];
        $parsedMeta = $item->parseDescription();
        $data['encoded_by_parsed'] = $parsedMeta['Encoded By'] ?? null;

        if (str_starts_with($item->id, 'REF-')) {
            $data['view_preset'] = 'lost_report';
            $meta = $item->parseDescription();
            $desc = $item->item_description ?? '';
            foreach (['Item', 'Item Type', 'Student Number', 'Full Name', 'Contact', 'Department', 'Owner', 'ID Type'] as $key) {
                $desc = preg_replace('/^' . preg_quote($key, '/') . ':\s*.+?(\n|$)/m', '', $desc);
            }
            $data['parsed'] = [
                'full_name'       => $meta['Full Name'] ?? null,
                'contact'         => $meta['Contact'] ?? null,
                'department'      => $meta['Department'] ?? null,
                'student_number'  => $meta['Student Number'] ?? null,
                'item'            => $meta['Item'] ?? $meta['Item Type'] ?? null,
                'clean_description' => trim(preg_replace('/\n+/', "\n", $desc)) ?: null,
            ];
            $data['display_ticket_id'] = $item->display_ticket_id;
            $data['linked_summary'] = null;
            if (! empty($item->matched_barcode_id)) {
                $data['linked_summary'] = ['matched_found_item_id' => $item->matched_barcode_id];
            }
        } else {
            $data['view_preset'] = $item->item_type === 'ID & Nameplate' ? 'found_external' : 'found_internal';
            $meta = $item->parseDescription();
            $desc = $item->item_description ?? '';
            foreach (['Item', 'Encoded By'] as $key) {
                $desc = preg_replace('/^' . preg_quote($key, '/') . ':\s*.+?(\n|$)/m', '', $desc);
            }
            $data['parsed'] = [
                'item'              => $meta['Item'] ?? null,
                'clean_description' => trim(preg_replace('/\n+/', "\n", $desc)) ?: null,
            ];
            $data['display_ticket_id'] = $item->id;
            $data['date_found'] = $item->date_encoded?->format('m/d/y');
            $data['linked_summary'] = null;
            $lost = Item::lostReports()->where('matched_barcode_id', $item->id)->orderByDesc('created_at')->first(['id']);
            if ($lost) {
                $data['linked_summary'] = ['matched_lost_report_id' => $lost->id];
            }
        }

        $data['date_lost'] = $item->date_lost?->format('m/d/y');
        $data['date_encoded'] = $item->date_encoded?->format('m/d/y');

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /** POST /admin/encode — AJAX encode found item (same behavior as POST /admin/found-items) */
    public function encode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_id'       => 'required|string|max:50',
            'category'         => 'nullable|string|max:100',
            'item'             => 'required|string|max:200',
            'color'            => ['required', 'string', 'max:100', Rule::in(ReportColors::ALLOWED)],
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
            $barcodeId = $result['item']->id;
            $linkedId = $result['linked_report_id'];

            return response()->json([
                'ok' => true,
                'id' => $barcodeId,
                'auto_match' => [
                    'linked' => $linkedId !== null,
                    'lost_report_id' => $linkedId,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 409);
        }
    }

    /** POST /admin/link — AJAX link found item to lost report */
    public function linkTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_id' => 'required|string|max:50',
            'ticket_id'  => 'required|string|max:50',
        ]);

        $foundItem  = Item::findOrFail($validated['barcode_id']);
        $lostReport = Item::findOrFail($validated['ticket_id']);

        \Illuminate\Support\Facades\DB::transaction(function () use ($foundItem, $lostReport) {
            $foundItem->update(['status' => 'For Verification']);
            $lostReport->update([
                'status'             => 'For Verification',
                'matched_barcode_id' => $foundItem->id,
            ]);

            DB::table('item_matches')->insertOrIgnore([
                'found_item_id'  => $foundItem->id,
                'lost_report_id' => $lostReport->id,
                'linked_at'      => now(),
            ]);

            \App\Models\Notification::notifyAdmin(
                'item_matched',
                'Item Matched',
                "Found item {$foundItem->id} linked to lost report {$lostReport->id}.",
                $foundItem->id
            );

            \App\Models\ActivityLog::record('matched', $foundItem->id, "Linked to {$lostReport->id}", session('admin_id'), 'admin');
        });

        return response()->json(['ok' => true]);
    }
}
