<?php

namespace App\Support;

use App\Models\Claim;
use App\Models\Item;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds JSON-safe payloads for student match cards and compare modals.
 */
class StudentMatchPayload
{
    /**
     * Whether this pair should appear in Recently Matched / Matched Reports.
     * Completed claims (found item claimed, or lost report resolved) belong in Claim History instead.
     */
    public static function shouldIncludeInMatchedLists(Item $lost, Item $found): bool
    {
        if ($found->status === 'Claimed') {
            return false;
        }

        if (in_array($lost->status, ['Resolved', 'Claimed', 'Cancelled'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Load this student's matched lost-report / found-item pairs.
     * Shared by DashboardController and LostReportController so the same
     * set of matches appears in "Recently Matched Item" and "Matched Reports".
     *
     * @return Collection<int, array{lost_report: Item, found_item: Item}>
     */
    public static function matchedPairsForStudent(?string $studentEmail, $studentId, ?string $categoryFilter = null): Collection
    {
        $studentNumber = Student::find($studentId)?->student_id;
        $userIds = array_values(array_filter([
            $studentEmail,
            $studentNumber ? $studentNumber . '@ub.edu.ph' : null,
        ]));

        return Item::lostReports()
            ->whereIn('user_id', $userIds)
            ->where('status', '!=', 'Cancelled')
            ->whereNotNull('matched_barcode_id')
            ->when($categoryFilter, fn ($q) => $q->where('item_type', $categoryFilter))
            ->orderByDesc('date_lost')
            ->get()
            ->map(function (Item $r) {
                $r->matched_found_item = Item::find($r->matched_barcode_id);
                return $r;
            })
            ->filter(fn (Item $r) => $r->matched_found_item !== null)
            ->filter(fn (Item $r) => self::shouldIncludeInMatchedLists($r, $r->matched_found_item))
            ->map(fn (Item $r) => [
                'lost_report' => $r,
                'found_item'  => $r->matched_found_item,
            ])
            ->values();
    }

    /**
     * @param Collection<int, array{lost_report: Item, found_item: Item}> $matchedPairs
     * @return array<int, array<string, mixed>>
     */
    public static function fromPairs(Collection $matchedPairs): array
    {
        return $matchedPairs->map(function (array $pair) {
            /** @var Item $lost */
            $lost = $pair['lost_report'];
            /** @var Item $found */
            $found = $pair['found_item'];

            $claimIntentSubmitted = Claim::where('found_item_id', $found->id)
                ->where('lost_report_id', $lost->id)
                ->whereIn('status', ['Pending', 'Approved'])
                ->exists();

            return [
                'lost_id'             => $lost->id,
                'found_id'            => $found->id,
                'lost_ticket_display' => $lost->display_ticket_id,
                'claimable'           => $found->status !== 'Claimed',
                'claim_intent_submitted' => $claimIntentSubmitted,
                'card_title'          => self::pairCardTitle($lost, $found),
                'card_description'    => self::pairCardDescription($lost),
                'card_location'       => $found->found_at ?? $found->storage_location ?? '—',
                'card_date'           => $found->date_encoded ? $found->date_encoded->format('m/d/y') : '—',
                'found'               => self::modalFieldBlock($found, true),
                'lost'                => self::modalFieldBlock($lost, false),
            ];
        })->values()->all();
    }

    private static function pairCardTitle(Item $lost, Item $found): string
    {
        $lm = $lost->parseDescription();
        $fm = $found->parseDescription();

        return $lm['Item']
            ?? $lm['Item Type']
            ?? $lost->item_type
            ?? $fm['Item']
            ?? $fm['Item Type']
            ?? $found->item_type
            ?? 'Item';
    }

    private static function pairCardDescription(Item $lost): string
    {
        $raw = (string) ($lost->item_description ?? '');
        $lines = preg_split('/\R/', $raw) ?: [];
        $buf = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^(Student Number|Item Type|Contact|Department|Full Name):\s*/i', $line)) {
                continue;
            }
            $buf[] = $line;
        }
        $text = implode(' ', $buf);
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';

        return Str::limit($text, 120, '…');
    }

    /**
     * @return array<string, string>
     */
    private static function modalFieldBlock(Item $item, bool $isFound): array
    {
        $meta = $item->parseDescription();
        $itemLabel = $meta['Item'] ?? $meta['Item Type'] ?? $item->item_type ?? '—';

        $dateVal = $isFound
            ? ($item->date_encoded ? $item->date_encoded->format('m/d/y') : '—')
            : ($item->date_lost ? $item->date_lost->format('m/d/y') : '—');

        $description = $isFound
            ? ($item->item_description ?? '—')
            : self::pairCardDescription($item);

        return [
            'category'    => $item->item_type ?? '—',
            'item'        => $itemLabel,
            'color'       => $item->color ?? '—',
            'brand'       => $item->brand ?? '—',
            'description' => $description ?: '—',
            'date'        => $dateVal,
            'date_key'    => $isFound ? 'Date Found' : 'Date Lost',
        ];
    }
}
