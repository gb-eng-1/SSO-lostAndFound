<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessGuide;
use App\Models\SupportContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only API endpoints for support contacts and process guides.
 * Ported from api/routes/support.php.
 */
class SupportController extends Controller
{
    /** GET /api/support/contacts */
    public function contacts(): JsonResponse
    {
        $contacts = SupportContact::orderBy('name')->get();
        return response()->json(['ok' => true, 'data' => $contacts]);
    }

    /** GET /api/support/guides */
    public function guides(): JsonResponse
    {
        $guides = ProcessGuide::orderBy('section')->orderBy('step_number')->get();
        return response()->json(['ok' => true, 'data' => $guides]);
    }

    /** GET /api/support/guides/{section} — get guides for a specific section */
    public function guidesBySection(string $section): JsonResponse
    {
        $validSections = ['report_lost', 'search_found', 'claim_item'];
        if (!in_array($section, $validSections, true)) {
            return response()->json(['ok' => false, 'error' => 'Invalid section.'], 400);
        }

        $guides = ProcessGuide::bySection($section)->get();
        return response()->json(['ok' => true, 'section' => $section, 'data' => $guides]);
    }
}
