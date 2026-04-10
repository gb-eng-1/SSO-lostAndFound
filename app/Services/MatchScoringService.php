<?php

namespace App\Services;

use App\Models\Item;

/**
 * Shared scoring logic for matching lost reports to found items.
 * Single source of truth — replaces duplicate implementations in
 * MatchedItemController and StudentReportController.
 */
class MatchScoringService
{
    public const MIN_SCORE = 40;

    /**
     * Score how well a found item matches a lost report.
     * Max possible score: 100.
     *   - Category match:       20 pts
     *   - Item-type match:      20 pts
     *   - Color match:          20 pts
     *   - Brand match:          20 pts
     *   - Description Jaccard:  0–20 pts
     */
    public function score(Item $report, Item $found): int
    {
        $score = 0;

        $cat   = trim($report->item_type ?? '');
        $color = trim($report->color ?? '');
        $brand = trim($report->brand ?? '');
        $reportDesc     = $report->item_description ?? '';
        $reportItemType = $this->extractItemType($reportDesc);

        $fCat      = trim($found->item_type ?? '');
        $fColor    = trim($found->color ?? '');
        $fBrand    = trim($found->brand ?? '');
        $fDesc     = $found->item_description ?? '';
        $fItemType = $this->extractItemType($fDesc);

        if ($cat && $fCat && strcasecmp($cat, $fCat) === 0) {
            $score += 20;
        }
        if ($reportItemType && $fItemType && strcasecmp($reportItemType, $fItemType) === 0) {
            $score += 20;
        }
        if ($color && $fColor && strcasecmp($color, $fColor) === 0) {
            $score += 20;
        }
        if ($brand && $fBrand && strcasecmp($brand, $fBrand) === 0) {
            $score += 20;
        }
        $score += (int) round($this->descriptionSimilarity($reportDesc, $fDesc) * 20);

        return $score;
    }

    private function extractItemType(string $desc): string
    {
        if (preg_match('/^Item(?:\s+Type)?:\s*(.+?)(?:\n|$)/m', $desc, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function descriptionSimilarity(string $a, string $b): float
    {
        $stopPrefixes = [
            'item type:', 'item:', 'student number:', 'contact:',
            'department:', 'owner:', 'id type:',
            '--- claim record ---', 'claimed by:', 'email:', 'date accomplished:',
        ];

        $clean = function (string $t) use ($stopPrefixes): array {
            $t = strtolower(strip_tags($t));
            foreach ($stopPrefixes as $p) {
                $t = str_replace($p, ' ', $t);
            }
            preg_match_all('/[a-z]{4,}/', $t, $m);
            return array_unique($m[0]);
        };

        $w1 = $clean($a);
        $w2 = $clean($b);

        if (!$w1 || !$w2) {
            return 0.0;
        }

        $inter = array_intersect($w1, $w2);
        $union = array_unique(array_merge($w1, $w2));

        return count($inter) / count($union);
    }
}
