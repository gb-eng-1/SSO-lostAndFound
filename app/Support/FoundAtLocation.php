<?php

namespace App\Support;

/**
 * Merge campus dropdown + optional "Found In" detail into a single stored string.
 */
class FoundAtLocation
{
    /** @return list<string> */
    public static function campusOptions(): array
    {
        return config('lost_found.campus_locations', []);
    }

    public static function merge(?string $campus, ?string $foundIn): ?string
    {
        $campus = trim((string) $campus);
        $foundIn = trim((string) $foundIn);
        if ($campus === '' && $foundIn === '') {
            return null;
        }
        if ($campus === '') {
            return $foundIn !== '' ? $foundIn : null;
        }
        if ($foundIn === '') {
            return $campus;
        }

        return $campus.' — '.$foundIn;
    }

    /**
     * Split stored DB value for form autofill (campus select + Found In text).
     *
     * @return array{campus: string, found_in: string}
     */
    public static function split(?string $stored): array
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return ['campus' => '', 'found_in' => ''];
        }
        foreach (self::campusOptions() as $c) {
            if ($stored === $c) {
                return ['campus' => $c, 'found_in' => ''];
            }
            $prefix = $c.' — ';
            if (str_starts_with($stored, $prefix)) {
                return ['campus' => $c, 'found_in' => substr($stored, strlen($prefix))];
            }
        }

        return ['campus' => '', 'found_in' => $stored];
    }

    public static function isValidCampus(?string $campus): bool
    {
        $campus = trim((string) $campus);
        if ($campus === '') {
            return true;
        }

        return in_array($campus, self::campusOptions(), true);
    }
}
