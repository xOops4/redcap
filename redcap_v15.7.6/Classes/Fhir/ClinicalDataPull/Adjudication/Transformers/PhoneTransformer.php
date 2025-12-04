<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Transformers;

use Vanderbilt\REDCap\Classes\Validation\ValidationTypes as VT;

/**
 * Phone transformer (North America)
 * - Removes leading +1 or 1 for 11-digit inputs
 * - Extracts extensions like x1234, ext 1234, #1234
 * - Produces:
 *   - display: (AAA) BBB-CCCC xEXT
 *   - save: AAA-BBB-CCCC xEXT (passes REDCap phone regex)
 *
 * Examples
 *   '+1 (615) 322-2222 ext 123' -> display '(615) 322-2222 x123', save '615-322-2222 x123'
 *   '615.322.2222' -> display '(615) 322-2222', save '615-322-2222'
 */
class PhoneTransformer implements TransformerInterface
{
    private const SUPPORTED = [VT::PHONE, VT::PHONE800];

    public function supports(string $validationType): bool
    {
        return in_array($validationType, self::SUPPORTED, true);
    }

    public function normalize(string $rawValue, string $validationType): ?array
    {
        $raw = trim($rawValue);
        if ($raw === '') return null;

        // Extract and remove extension from the end (e.g., x1234, ext 1234, #1234)
        $ext = null;
        if (preg_match('/(?:#|x\.?|ext\.?|extension)\s*(\d+)\s*$/i', $raw, $m)) {
            $ext = $m[1];
            $raw = substr($raw, 0, -strlen($m[0]));
        }

        // Remove leading +1 or 1 from 11-digit numbers
        // Get all digits
        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === null) return null;

        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return null; // let RegexValidator flag as invalid
        }

        $npa = substr($digits, 0, 3);
        $nxx = substr($digits, 3, 3);
        $line = substr($digits, 6, 4);

        // Save-ready (aim to pass REDCap's phone regex)
        $save = sprintf('%s-%s-%s', $npa, $nxx, $line);
        if ($ext) $save .= ' x' . $ext;

        // Display-friendly
        $display = sprintf('(%s) %s-%s', $npa, $nxx, $line);
        if ($ext) $display .= ' x' . $ext;

        return ['display' => $display, 'save' => $save];
    }
}
