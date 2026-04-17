<?php

namespace App\Support;

/**
 * Whether stored item/claim image values are safe to use as an HTML img src.
 */
final class ItemImageDisplay
{
    public static function canUseAsImgSrc(?string $v): bool
    {
        if (! is_string($v) || $v === '') {
            return false;
        }

        if (str_starts_with($v, 'data:image/')) {
            return true;
        }

        return (bool) preg_match('#^https?://#i', $v);
    }

    /** Bell / list thumbnails: keep base64 bounded; URLs stay short. */
    public static function canUseAsBellThumbnail(?string $v): bool
    {
        if (! is_string($v) || $v === '') {
            return false;
        }

        if (str_starts_with($v, 'data:image/')) {
            return strlen($v) < 120_000;
        }

        if (preg_match('#^https?://#i', $v)) {
            return strlen($v) < 2048;
        }

        return false;
    }
}
