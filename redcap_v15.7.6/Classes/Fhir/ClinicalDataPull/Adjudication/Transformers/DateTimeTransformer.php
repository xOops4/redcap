<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Transformers;

use Vanderbilt\REDCap\Classes\Validation\ValidationTypes as VT;

/**
 * Date/Datetime transformer
 * - Accepts ISO-like inputs (YYYY-MM-DD, YYYY-MM-DD[ T]HH:MM[:SS], optional Z/offset)
 * - No timezone shifting; parses as-is
 * - Produces:
 *   - display: matches REDCap validation type (ymd/mdy/dmy, with/without seconds)
 *   - save: Y-m-d, Y-m-d H:i, or Y-m-d H:i:s (depending on validation)
 *
 * Examples
 *   normalize('2025-09-12', 'date_mdy')
 *     => display: '09-12-2025', save: '2025-09-12'
 *   normalize('2025-09-12T09:43:59Z', 'datetime_ymd')
 *     => display: '2025-09-12 09:43', save: '2025-09-12 09:43'
 *   normalize('2025-09-12 09:43', 'datetime_seconds_ymd')
 *     => display: '2025-09-12 09:43:00', save: '2025-09-12 09:43:00'
 */
class DateTimeTransformer implements TransformerInterface
{
    /**
     * List of supported REDCap validation types for dates/datetimes
     */
    private const SUPPORTED = VT::DATE_TYPES;

    public function supports(string $validationType): bool
    {
        return in_array($validationType, self::SUPPORTED, true);
    }

    public function normalize(string $rawValue, string $validationType): ?array
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') return null;

        $parsed = $this->parseTolerant($rawValue);
        if (!$parsed) return null;

        // Display and save format per validation type
        switch ($validationType) {
            case VT::DATE_YMD:
                $display = sprintf('%04d-%02d-%02d', $parsed['Y'], $parsed['m'], $parsed['d']);
                $save = $display; // Y-m-d
                break;
            case VT::DATE_MDY:
                $display = sprintf('%02d-%02d-%04d', $parsed['m'], $parsed['d'], $parsed['Y']);
                $save = $display; // m-d-Y
                break;
            case VT::DATE_DMY:
                $display = sprintf('%02d-%02d-%04d', $parsed['d'], $parsed['m'], $parsed['Y']);
                $save = $display; // d-m-Y
                break;
            case VT::DATETIME_YMD:
                $display = sprintf('%04d-%02d-%02d %02d:%02d', $parsed['Y'], $parsed['m'], $parsed['d'], $parsed['H'], $parsed['i']);
                $save = $display; // Y-m-d H:i
                break;
            case VT::DATETIME_MDY:
                $display = sprintf('%02d-%02d-%04d %02d:%02d', $parsed['m'], $parsed['d'], $parsed['Y'], $parsed['H'], $parsed['i']);
                $save = $display; // m-d-Y H:i
                break;
            case VT::DATETIME_DMY:
                $display = sprintf('%02d-%02d-%04d %02d:%02d', $parsed['d'], $parsed['m'], $parsed['Y'], $parsed['H'], $parsed['i']);
                $save = $display; // d-m-Y H:i
                break;
            case VT::DATETIME_SECONDS_YMD:
                $display = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $parsed['Y'], $parsed['m'], $parsed['d'], $parsed['H'], $parsed['i'], $parsed['s']);
                $save = $display; // Y-m-d H:i:s
                break;
            case VT::DATETIME_SECONDS_MDY:
                $display = sprintf('%02d-%02d-%04d %02d:%02d:%02d', $parsed['m'], $parsed['d'], $parsed['Y'], $parsed['H'], $parsed['i'], $parsed['s']);
                $save = $display; // m-d-Y H:i:s
                break;
            case VT::DATETIME_SECONDS_DMY:
                $display = sprintf('%02d-%02d-%04d %02d:%02d:%02d', $parsed['d'], $parsed['m'], $parsed['Y'], $parsed['H'], $parsed['i'], $parsed['s']);
                $save = $display; // d-m-Y H:i:s
                break;
            default: return null;
        }

        return ['display' => $display, 'save' => $save];
    }

    /**
     * Parse common EHR timestamp forms without timezone shifting.
     * Accepts:
     *  - YYYY-MM-DD
     *  - YYYY-MM-DD[ T]HH:MM
     *  - YYYY-MM-DD[ T]HH:MM:SS
     *  - Optional trailing timezone offset or Z (ignored for formatting)
     *  - Also try loose parsing for m/d/Y and d/m/Y when separators imply them
     */
    private function parseTolerant(string $value): ?array
    {
        $v = trim($value);
        if ($v === '') return null;

        // Normalize separators: replace 'T' with space
        $v = str_replace('T', ' ', $v);
        $v = preg_replace('/\s+/', ' ', $v);

        // Strip timezone suffix if present (e.g., +00:00, -0500, Z)
        if (preg_match('/^(.*?)(Z|[+-]\d{2}:?\d{2})$/', $v, $m)) {
            $v = trim($m[1]);
        }

        // If only date provided
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
            return [
                'Y' => (int)$m[1], 'm' => (int)$m[2], 'd' => (int)$m[3],
                'H' => 0, 'i' => 0, 's' => 0,
            ];
        }

        // Datetime with seconds or minutes (Y-m-d HH:MM[:SS])
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2})(?::(\d{2}))?$/', $v, $m)) {
            return [
                'Y' => (int)$m[1], 'm' => (int)$m[2], 'd' => (int)$m[3],
                'H' => (int)$m[4], 'i' => (int)$m[5], 's' => isset($m[6]) ? (int)$m[6] : 0,
            ];
        }

        // Try to parse common MDY/DMY date-only inputs (e.g., 12/31/2020 or 31-12-2020)
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $v, $m)) {
            // Heuristic: if first > 12 => DMY; else assume MDY
            $a = (int)$m[1]; $b = (int)$m[2]; $Y = (int)$m[3];
            if ($a > 12) { $d = $a; $mth = $b; } else { $mth = $a; $d = $b; }
            return [ 'Y' => $Y, 'm' => $mth, 'd' => $d, 'H' => 0, 'i' => 0, 's' => 0 ];
        }

        // Fallback: try to parse loose formats if possible
        // Avoid DateTime timezone shifting; just extract digits order
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})(?:\s(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/', $v, $m)) {
            return [
                'Y' => (int)$m[1], 'm' => (int)$m[2], 'd' => (int)$m[3],
                'H' => isset($m[4]) ? (int)$m[4] : 0,
                'i' => isset($m[5]) ? (int)$m[5] : 0,
                's' => isset($m[6]) ? (int)$m[6] : 0,
            ];
        }

        return null;
    }
}
