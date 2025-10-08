<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Transformers;

/**
 * Contract for value transformers used by the NormalizationValidator.
 *
 * Purpose
 * - Convert raw EHR values into:
 *   - display: a user-friendly string for the adjudication table (matches field validation format)
 *   - save: a storage-ready string for the input value (what gets submitted/saved)
 *
 * Example (date):
 *   normalize('2025-09-12T09:43:59Z', 'datetime_seconds_ymd')
 *   => ['display' => '2025-09-12 09:43:59', 'save' => '2025-09-12 09:43:59']
 *
 * Example (phone):
 *   normalize('+1 (615) 322-2222 ext 123', 'phone')
 *   => ['display' => '(615) 322-2222 x123', 'save' => '615-322-2222 x123']
 */
interface TransformerInterface
{
    /**
     * Return true if this transformer supports the given REDCap validation type
     */
    public function supports(string $validationType): bool;

    /**
     * Normalize a raw source value into:
     * - display: formatted for UI matching the field's validation format
     * - save: canonical format suitable for submission/saving
     *
     * Return null if the value cannot be parsed/normalized.
     *
     * @return array{display:string, save:string}|null
     */
    public function normalize(string $rawValue, string $validationType): ?array;
}
