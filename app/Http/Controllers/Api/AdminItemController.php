<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API endpoints for admin item operations.
 * Ported from api/routes/items.php.
 */
class AdminItemController extends Controller
{
    /** GET /api/admin/items — list all found items with optional filters */
    public function index(Request $request): JsonResponse
    {
        $query = Item::foundItems()
            ->when($request->query('status'),   fn($q) => $q->where('status', $request->query('status')))
            ->when($request->query('category'), fn($q) => $q->where('item_type', $request->query('category')))
            ->when($request->query('search'),   fn($q) => $q->where(function ($inner) use ($request) {
                $s = $request->query('search');
                $inner->where('id', 'LIKE', "%{$s}%")
                      ->orWhere('item_description', 'LIKE', "%{$s}%");
            }))
            ->orderByDesc('date_encoded');

        $limit = min((int) $request->query('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json(['ok' => true, 'data' => $items, 'count' => $items->count()]);
    }

    /** GET /api/admin/items/{id} — fetch single item */
    public function show(string $id): JsonResponse
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['ok' => false, 'error' => 'Item not found.'], 404);
        }
        return response()->json(['ok' => true, 'data' => $item]);
    }

    /** GET /api/admin/stats — dashboard statistics */
    public function stats(): JsonResponse
    {
        $data = [
            'unclaimed'       => Item::foundItems()->where('status', 'Unclaimed Items')->count(),
            'for_verification' => Item::where('status', 'For Verification')->count(),
            'claimed'         => Item::foundItems()->where('status', 'Claimed')->count(),
            'lost_reports'    => Item::lostReports()->count(),
        ];

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /** PUT /api/admin/items/{id}/status — update item status */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:Unclaimed Items,For Verification,Unresolved Claimants,Matched,Claimed,Resolved,Cancelled,Disposed',
        ]);

        $item = Item::findOrFail($id);
        $item->update(['status' => $request->input('status')]);

        return response()->json(['ok' => true, 'id' => $id, 'status' => $item->status]);
    }
}
