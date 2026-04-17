<?php

namespace App\Support;

/**
 * Downscale and re-encode report/claim photos as JPEG data URLs to avoid huge SQL payloads
 * (MySQL max_allowed_packet) while keeping images displayable as before.
 *
 * When the PHP GD extension is not loaded, valid data URLs under MAX_DATA_URL_LENGTH are
 * stored unchanged; larger images require enabling GD or a smaller file.
 */
final class ReportImageNormalizer
{
    public const MAX_EDGE = 1600;

    public const JPEG_QUALITY = 82;

    /** Reject if output data URL is still larger than this (bytes of full string). */
    public const MAX_DATA_URL_LENGTH = 2_500_000;

    /** Reject raw base64 payload larger than this before decode (bytes). */
    public const MAX_INPUT_BINARY = 25 * 1024 * 1024;

    /**
     * @return string|null Normalized data:image/jpeg;base64,... (with GD), original data URL
     *                      (without GD, if under size cap), or null if input empty
     *
     * @throws \InvalidArgumentException Unreadable, corrupt, or still-too-large image
     */
    public static function normalize(?string $dataUrl): ?string
    {
        if ($dataUrl === null) {
            return null;
        }
        $dataUrl = trim($dataUrl);
        if ($dataUrl === '') {
            return null;
        }

        if (! str_starts_with($dataUrl, 'data:')) {
            throw new \InvalidArgumentException('Invalid photo format.');
        }

        if (! preg_match('#^data:image/[\w+.-]+;base64,(.+)$#i', $dataUrl, $m)) {
            throw new \InvalidArgumentException('Photo must be a base64-encoded image.');
        }

        $raw = base64_decode($m[1], true);
        if ($raw === false || $raw === '') {
            throw new \InvalidArgumentException('Could not read the photo. Try a smaller or different image.');
        }

        if (strlen($raw) > self::MAX_INPUT_BINARY) {
            throw new \InvalidArgumentException('Photo is too large. Please choose a smaller image.');
        }

        if (! extension_loaded('gd')) {
            if (strlen($dataUrl) > self::MAX_DATA_URL_LENGTH) {
                throw new \InvalidArgumentException(
                    'Photo is too large to store without the PHP GD extension. Enable extension=gd in php.ini (XAMPP: php/php.ini), restart Apache, or upload a smaller image.'
                );
            }

            return $dataUrl;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            throw new \InvalidArgumentException('Could not process the photo. Try JPEG or PNG.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($src);
            throw new \InvalidArgumentException('Invalid image dimensions.');
        }

        $maxEdge = self::MAX_EDGE;
        $scale = min(1.0, $maxEdge / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        if ($dst === false) {
            imagedestroy($src);
            throw new \InvalidArgumentException('Could not resize the photo.');
        }

        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();
        imagedestroy($dst);

        if ($jpeg === false || $jpeg === '') {
            throw new \InvalidArgumentException('Could not compress the photo.');
        }

        $out = 'data:image/jpeg;base64,' . base64_encode($jpeg);
        if (strlen($out) > self::MAX_DATA_URL_LENGTH) {
            throw new \InvalidArgumentException('Photo is still too large after compression. Try a different picture.');
        }

        return $out;
    }
}
