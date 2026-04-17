<?php

namespace App\Support;

use App\Services\GoogleDriveImageService;
use Illuminate\Support\Facades\Log;

/**
 * After {@see ReportImageNormalizer}, optionally upload to Google Drive and store an HTTPS URL.
 */
final class ReportImageStorage
{
    public static function storeAfterNormalize(?string $normalizedDataUrl, string $filenameStem): ?string
    {
        if ($normalizedDataUrl === null || $normalizedDataUrl === '') {
            return $normalizedDataUrl;
        }

        if (! config('services.google_drive.enabled')) {
            return $normalizedDataUrl;
        }

        try {
            $url = app(GoogleDriveImageService::class)->uploadFromDataUrl($normalizedDataUrl, $filenameStem);

            return ($url !== null && $url !== '') ? $url : $normalizedDataUrl;
        } catch (\Throwable $e) {
            Log::warning('Report image Drive upload failed, keeping data URL: '.$e->getMessage());

            return $normalizedDataUrl;
        }
    }
}
