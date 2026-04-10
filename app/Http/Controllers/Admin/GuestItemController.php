<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Support\ReportColors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Handles encoding of guest-surrendered ID items.
 * Ported from save_guest_item.php.
 * Accessible as a sub-action of FoundItemController but kept separate for clarity.
 */
class GuestItemController extends Controller
{
    /** POST /admin/found-items/guest */
    public function store(Request $request): JsonResponse
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

        Item::create([
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

        ActivityLog::record(
            'encoded',
            $barcodeId,
            "Guest item {$barcodeId} encoded by " . (session('admin_name') ?? 'admin'),
            session('admin_id'),
            'admin'
        );

        return response()->json(['ok' => true, 'id' => $barcodeId]);
    }
}
