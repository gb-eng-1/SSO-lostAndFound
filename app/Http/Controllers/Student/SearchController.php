<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    /** @var list<string> */
    private const TERMINAL_STATUSES = ['Claimed', 'Resolved', 'Cancelled', 'Disposed'];

    /** @return array<int, string> */
    private function studentUserIds(): array
    {
        $studentEmail = session('student_email');
        $studentId = session('student_id');
        $studentNumber = Student::find($studentId)?->student_id;

        return array_values(array_filter([
            $studentEmail,
            $studentNumber ? $studentNumber.'@ub.edu.ph' : null,
        ]));
    }

    /** GET /student/search/suggestions?q= */
    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['ok' => true, 'results' => []]);
        }

        $userIds = $this->studentUserIds();
        if ($userIds === []) {
            return response()->json(['ok' => true, 'results' => []]);
        }

        $like = '%'.addcslashes($q, '%_\\').'%';

        $myReports = Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->get(['id', 'matched_barcode_id']);

        $myReportIds = $myReports->pluck('id');
        $matchedFoundIds = $myReports->pluck('matched_barcode_id')->filter()->values();

        if (Schema::hasTable('item_matches')) {
            $extraFound = DB::table('item_matches')
                ->whereIn('lost_report_id', $myReportIds)
                ->pluck('found_item_id');
            $matchedFoundIds = $matchedFoundIds->merge($extraFound)->unique()->filter()->values();
        }

        $out = collect();

        $reportMatches = Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->whereNotIn('status', self::TERMINAL_STATUSES)
            ->where(function ($w) use ($like, $q) {
                $w->where('id', 'like', '%'.$q.'%')
                    ->orWhere('item_type', 'like', $like)
                    ->orWhere('brand', 'like', $like);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'item_type', 'status', 'item_description']);

        foreach ($reportMatches as $item) {
            $desc = (string) ($item->item_description ?? '');
            if (mb_strlen($desc) > 80) {
                $desc = mb_substr($desc, 0, 77).'…';
            }
            $out->push([
                'id' => $item->id,
                'kind' => 'report',
                'title' => $item->item_type ?? '—',
                'subtitle' => $desc !== '' ? $desc : '—',
                'meta' => $item->status ?? '',
            ]);
        }

        if ($matchedFoundIds->isNotEmpty()) {
            $foundMatches = Item::foundItems()
                ->whereIn('id', $matchedFoundIds)
                ->whereNotIn('status', self::TERMINAL_STATUSES)
                ->where(function ($w) use ($like, $q) {
                    $w->where('id', 'like', '%'.$q.'%')
                        ->orWhere('item_type', 'like', $like)
                        ->orWhere('item_description', 'like', $like)
                        ->orWhere('brand', 'like', $like);
                })
                ->orderByDesc('date_encoded')
                ->limit(10)
                ->get(['id', 'item_type', 'status', 'item_description']);

            foreach ($foundMatches as $item) {
                $desc = (string) ($item->item_description ?? '');
                if (mb_strlen($desc) > 80) {
                    $desc = mb_substr($desc, 0, 77).'…';
                }
                $out->push([
                    'id' => $item->id,
                    'kind' => 'found',
                    'title' => $item->item_type ?? '—',
                    'subtitle' => $desc !== '' ? $desc : '—',
                    'meta' => $item->status ?? '',
                ]);
            }
        }

        $results = $out->unique('id')->take(20)->values();

        return response()->json(['ok' => true, 'results' => $results]);
    }
}
