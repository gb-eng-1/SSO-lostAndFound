<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Student;
use App\Support\StudentMatchPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentItemController extends Controller
{
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

    /**
     * GET /student/item?id= — allowed lost reports + matched found items only.
     * Lost reports (REF-): include photo. Matched found items (UB…): include photo for Item Details.
     */
    public function show(Request $request): JsonResponse
    {
        $id = trim((string) $request->query('id', ''));
        if ($id === '') {
            return response()->json(['ok' => false, 'error' => 'Missing id'], 400);
        }

        $userIds = $this->studentUserIds();
        if ($userIds === []) {
            return response()->json(['ok' => false, 'error' => 'Unauthorized'], 403);
        }

        $item = Item::find($id);
        if (! $item) {
            return response()->json(['ok' => false, 'error' => 'Item not found'], 404);
        }

        if (str_starts_with($id, 'REF-')) {
            if (! $item->user_id || ! in_array($item->user_id, $userIds, true)) {
                return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
            }

            $meta = $item->parseDescription();
            $desc = $item->item_description ?? '';
            foreach (['Item', 'Item Type', 'Student Number', 'Full Name', 'Contact', 'Department', 'Owner', 'ID Type'] as $key) {
                $desc = preg_replace('/^' . preg_quote($key, '/') . ':\s*.+?(?:\r?\n|$)/m', '', $desc);
            }
            $fullName = $meta['Full Name'] ?? null;
            if ($fullName === null || $fullName === '') {
                $fullName = Student::where('email', $item->user_id)->value('name');
            }
            if (($fullName === null || $fullName === '') && session('student_id')) {
                $fullName = Student::find(session('student_id'))?->name;
            }
            if (($fullName === null || $fullName === '') && is_string($item->user_id)
                && preg_match('/^(\d+)@ub\.edu\.ph$/i', $item->user_id, $m)) {
                $fullName = Student::where('student_id', $m[1])->value('name');
            }

            $parsed = [
                'full_name'          => $fullName ? trim((string) $fullName) : null,
                'contact'            => $meta['Contact'] ?? null,
                'department'         => $meta['Department'] ?? null,
                'student_number'     => $meta['Student Number'] ?? null,
                'item'               => $meta['Item'] ?? $meta['Item Type'] ?? null,
                'clean_description'  => trim(preg_replace("/\n+/", "\n", $desc)) ?: null,
            ];

            $data = [
                'id'                 => $item->id,
                'display_ticket_id'  => $item->display_ticket_id,
                'item_type'          => $item->item_type,
                'color'              => $item->color,
                'brand'              => $item->brand,
                'date_lost'          => $item->date_lost?->format('m/d/y'),
                'status'             => $item->status,
                'item_description'   => $item->item_description,
                'image_data'         => $item->image_data,
                'parsed'             => $parsed,
            ];

            if (! empty($item->matched_barcode_id)) {
                $found = Item::find($item->matched_barcode_id);
                if ($found) {
                    $pair = $this->buildStudentMatchedPairPayload($item, $found);
                    if ($pair !== null) {
                        $data['view_preset'] = 'matched_pair';
                        $data['matched_pair'] = $pair;
                    }
                }
            }

            return response()->json(['ok' => true, 'data' => $data]);
        }

        // Found item (UB…) — student may view if linked to their lost report
        $allowed = Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->where('matched_barcode_id', $id)
            ->exists();

        if (! $allowed && Schema::hasTable('item_matches')) {
            $allowed = DB::table('item_matches')
                ->join('items as lr', 'item_matches.lost_report_id', '=', 'lr.id')
                ->where('item_matches.found_item_id', $id)
                ->whereIn('lr.user_id', $userIds)
                ->exists();
        }

        if (! $allowed) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $lost = Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->where('matched_barcode_id', $id)
            ->first();

        if (! $lost && Schema::hasTable('item_matches')) {
            $lostId = DB::table('item_matches')
                ->join('items as lr', 'item_matches.lost_report_id', '=', 'lr.id')
                ->where('item_matches.found_item_id', $id)
                ->whereIn('lr.user_id', $userIds)
                ->value('lr.id');
            if ($lostId) {
                $lost = Item::find($lostId);
            }
        }

        $meta = $item->parseDescription();
        $desc = $item->item_description ?? '';
        foreach (['Item', 'Encoded By'] as $key) {
            $desc = preg_replace('/^' . preg_quote($key, '/') . ':\s*.+?(\n|$)/m', '', $desc);
        }
        $cleanDescription = trim(preg_replace("/\n+/", "\n", $desc)) ?: null;

        $data = [
            'id' => $item->id,
            'display_ticket_id' => $item->id,
            'item_type' => $item->item_type,
            'color' => $item->color,
            'brand' => $item->brand,
            'found_at' => $item->found_at,
            'storage_location' => $item->storage_location,
            'found_by' => $item->found_by,
            'encoded_by' => $meta['Encoded By'] ?? null,
            'date_found' => $item->date_encoded?->format('m/d/y'),
            'date_encoded' => $item->date_encoded?->format('m/d/y') ?? '—',
            'status' => $item->status,
            'item_description' => $item->item_description,
            'image_data' => $item->image_data,
            'parsed' => [
                'item' => $meta['Item'] ?? null,
                'clean_description' => $cleanDescription,
            ],
        ];

        if ($lost) {
            $pair = $this->buildStudentMatchedPairPayload($lost, $item);
            if ($pair !== null) {
                $data['view_preset'] = 'matched_pair';
                $data['matched_pair'] = $pair;
            }
        }

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildStudentMatchedPairPayload(Item $lostReport, Item $foundItem): ?array
    {
        $rows = StudentMatchPayload::fromPairs(collect([
            ['lost_report' => $lostReport, 'found_item' => $foundItem],
        ]));

        if ($rows === []) {
            return null;
        }

        return $rows[0];
    }
}
