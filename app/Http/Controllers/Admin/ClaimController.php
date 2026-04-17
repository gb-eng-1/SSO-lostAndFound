<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Notification;
use App\Support\ReportImageNormalizer;
use App\Support\ReportImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Handles the admin "Confirm Item Claim" action.
 * Ported from ADMIN/claim_item.php.
 *
 * Marks the found item as 'Claimed', appends claimant details to item_description,
 * and resolves any linked REF- lost report.
 */
class ClaimController extends Controller
{
    /** POST /admin/claim/{id} */
    public function confirm(Request $request, string $id): JsonResponse
    {
        // Determine item type before validation to apply context-sensitive email rules
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['ok' => false, 'error' => "Item '{$id}' not found."], 404);
        }

        $isExternalId = $item->item_type === 'ID & Nameplate';

        $emailRule = $isExternalId
            ? 'nullable|email'
            : ['nullable', 'email', 'regex:/@ub\.edu\.ph$/i'];

        $validated = $request->validate([
            'claimant_name'     => 'required|string|max:200',
            'ub_mail'           => $emailRule,
            'contact_number'    => 'nullable|string|max:50',
            'date_accomplished' => 'nullable|date|before_or_equal:today',
            'imageDataUrl'      => 'required|string',
        ]);

        if ($item->requiresStudentClaimIntentBeforeAdminClaim() && ! $item->hasStudentClaimIntentForAdminClaim()) {
            return response()->json([
                'ok'    => false,
                'error' => 'The student who lost this item has not yet confirmed in the app that they will claim it. Wait until they acknowledge their claim before completing this step.',
                'code'  => 'student_claim_intent_required',
            ], 422);
        }

        $note  = "\n\n--- Claim Record ---";
        $note .= "\nClaimed By: " . $validated['claimant_name'];
        if (!empty($validated['ub_mail'])) {
            $note .= "\nEmail: " . $validated['ub_mail'];
        }
        if (!empty($validated['contact_number'])) {
            $note .= "\nContact: " . $validated['contact_number'];
        }
        if (!empty($validated['date_accomplished'])) {
            $note .= "\nDate Accomplished: " . $validated['date_accomplished'];
        }

        try {
            $normalized = ReportImageNormalizer::normalize($validated['imageDataUrl']);
            $claimImage = ReportImageStorage::storeAfterNormalize($normalized, 'admin-claim-'.$item->id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        if ($claimImage === null) {
            return response()->json(['ok' => false, 'error' => 'Claim photo is required.'], 422);
        }

        DB::transaction(function () use ($item, $note, $validated, $claimImage) {
            $item->update([
                'status'           => 'Claimed',
                'item_description' => ($item->item_description ?? '') . $note,
                'image_data'       => $claimImage,
            ]);

            // Resolve any linked REF- lost report
            Item::lostReports()
                ->where('matched_barcode_id', $item->id)
                ->whereNotIn('status', ['Resolved', 'Claimed'])
                ->update(['status' => 'Resolved']);

            Notification::notifyAdmin(
                'item_claimed',
                'Item Claimed',
                "Found item {$item->id} has been claimed by {$validated['claimant_name']}.",
                $item->id
            );

            ActivityLog::record(
                'claimed',
                $item->id,
                "Claimed by {$validated['claimant_name']}" . (!empty($validated['ub_mail']) ? " ({$validated['ub_mail']})" : ''),
                session('admin_id'),
                'admin'
            );
        });

        return response()->json(['ok' => true, 'id' => $id]);
    }
}
