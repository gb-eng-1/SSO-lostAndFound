<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** @var list<string> */
    private const TERMINAL_STATUSES = ['Claimed', 'Resolved', 'Cancelled', 'Disposed'];

    /** GET /admin/search/suggestions?q= */
    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json(['ok' => true, 'results' => []]);
        }

        $like = '%'.addcslashes($q, '%_\\').'%';

        $items = Item::query()
            ->whereNotIn('status', self::TERMINAL_STATUSES)
            ->where(function ($w) use ($like, $q) {
                $w->where('id', 'like', '%'.$q.'%')
                    ->orWhere('item_type', 'like', $like)
                    ->orWhere('item_description', 'like', $like)
                    ->orWhere('brand', 'like', $like);
            })
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'item_type', 'status', 'item_description']);

        $results = $items->map(function (Item $item) {
            $kind = str_starts_with($item->id, 'REF-') ? 'report' : 'found';
            $desc = (string) ($item->item_description ?? '');
            if (mb_strlen($desc) > 80) {
                $desc = mb_substr($desc, 0, 77).'…';
            }

            return [
                'id' => $item->id,
                'kind' => $kind,
                'title' => $item->item_type ?? '—',
                'subtitle' => $desc !== '' ? $desc : '—',
                'meta' => $item->status ?? '',
            ];
        })->values();

        return response()->json(['ok' => true, 'results' => $results]);
    }
}
