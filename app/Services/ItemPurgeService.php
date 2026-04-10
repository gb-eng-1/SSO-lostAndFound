<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permanently removes an item row and dependent rows (matches, claims, notifications).
 * Used when a student or admin cancels/deletes a report or found item.
 */
class ItemPurgeService
{
    public function purge(string $id): void
    {
        DB::transaction(function () use ($id) {
            $item = Item::query()->find($id);
            if (! $item) {
                return;
            }

            $foundIdsToMaybeRevert = collect();

            if (str_starts_with($id, 'REF-')) {
                if ($item->matched_barcode_id) {
                    $foundIdsToMaybeRevert->push($item->matched_barcode_id);
                }
                if (Schema::hasTable('item_matches')) {
                    $foundIdsToMaybeRevert = $foundIdsToMaybeRevert->merge(
                        DB::table('item_matches')->where('lost_report_id', $id)->pluck('found_item_id')
                    );
                }
            } else {
                Item::query()->where('matched_barcode_id', $id)->update(['matched_barcode_id' => null]);
            }

            if (Schema::hasTable('item_matches')) {
                DB::table('item_matches')->where(function ($q) use ($id) {
                    $q->where('found_item_id', $id)->orWhere('lost_report_id', $id);
                })->delete();
            }

            if (Schema::hasTable('matches')) {
                DB::table('matches')->where(function ($q) use ($id) {
                    $q->where('found_item_id', $id)->orWhere('lost_report_id', $id);
                })->delete();
            }

            Claim::query()->where(function ($q) use ($id) {
                $q->where('found_item_id', $id)->orWhere('lost_report_id', $id);
            })->delete();

            Notification::query()->where('related_id', $id)->delete();

            DB::table('activity_log')->where('item_id', $id)->delete();

            $item->delete();

            if (str_starts_with($id, 'REF-')) {
                foreach ($foundIdsToMaybeRevert->filter()->unique() as $fid) {
                    $remaining = Schema::hasTable('item_matches')
                        ? (int) DB::table('item_matches')->where('found_item_id', $fid)->count()
                        : 0;
                    if ($remaining === 0) {
                        Item::query()->where('id', $fid)->where('status', 'For Verification')->update(['status' => 'Unclaimed Items']);
                    }
                }
            }
        });
    }
}
