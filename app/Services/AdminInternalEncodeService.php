<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Support\FoundAtLocation;
use App\Support\ReportImageNormalizer;
use App\Support\ReportImageStorage;

/**
 * Single code path for admin "internal" found-item encode (Item Recovered Report).
 * Used by FoundItemController::store and DashboardController::encode.
 */
class AdminInternalEncodeService
{
    /**
     * Create a found item from validated request fields (same keys as FoundItemController::store).
     *
     * @param  array<string, mixed>  $validated
     * @return array{item: Item, linked_report_id: string|null}
     *
     * @throws \InvalidArgumentException Validation / merge failures
     * @throws \RuntimeException Duplicate barcode
     */
    public function createFromValidated(array $validated): array
    {
        if (! FoundAtLocation::isValidCampus($validated['found_at'] ?? null)) {
            throw new \InvalidArgumentException('Invalid campus for Found At.');
        }

        $mergedFoundAt = FoundAtLocation::merge($validated['found_at'] ?? null, $validated['found_in'] ?? null);
        if ($mergedFoundAt !== null && strlen($mergedFoundAt) > 200) {
            throw new \InvalidArgumentException('Found At and Found In combined are too long.');
        }

        $barcodeId = trim((string) ($validated['barcode_id'] ?? ''));
        if ($barcodeId === '') {
            throw new \InvalidArgumentException('Barcode ID is required.');
        }
        if (str_starts_with($barcodeId, 'REF-')) {
            throw new \InvalidArgumentException('Invalid Barcode ID.');
        }

        if (Item::find($barcodeId)) {
            throw new \RuntimeException("Barcode '{$barcodeId}' already exists.");
        }

        $desc = trim((string) ($validated['item_description'] ?? ''));
        if (! empty($validated['item'] ?? '')) {
            $desc = 'Item: ' . trim((string) $validated['item']) . ($desc ? "\n" . $desc : '');
        }
        $encoder = trim((string) session('admin_name', ''));
        if ($encoder !== '') {
            $desc .= ($desc !== '' ? "\n" : '') . 'Encoded By: ' . $encoder;
        }

        $normalized = ReportImageNormalizer::normalize($validated['imageDataUrl'] ?? null);
        $imageData = ReportImageStorage::storeAfterNormalize($normalized, 'found-'.$barcodeId);

        $item = Item::create([
            'id'               => $barcodeId,
            'item_type'        => $validated['category'] ?? null,
            'item_description' => $desc ?: null,
            'color'            => $validated['color'],
            'brand'            => $validated['brand'] ?? null,
            'storage_location' => $validated['storage_location'] ?? null,
            'found_at'         => $mergedFoundAt,
            'found_by'         => $validated['found_by'] ?? null,
            'date_encoded'     => $validated['date_found'] ?? now()->toDateString(),
            'image_data'       => $imageData,
            'status'           => 'Unclaimed Items',
        ]);

        ActivityLog::record('encoded', $barcodeId, "Encoded internal item {$barcodeId}", session('admin_id'), 'admin');

        $linkedReportId = (new AutoMatchService(new MatchScoringService()))->runForFoundItem($item);

        return ['item' => $item, 'linked_report_id' => $linkedReportId];
    }
}
