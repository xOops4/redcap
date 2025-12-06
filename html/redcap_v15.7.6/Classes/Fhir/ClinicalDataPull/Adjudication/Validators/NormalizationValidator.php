<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Validators;

use Project;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\DTOs\FieldDataDTO;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Transformers\TransformerRegistry;

/**
 * A generic normalization validator that uses a TransformerRegistry
 * to convert raw source values into:
 *  - display: formatted for UI based on the field's validation type
 *  - save: normalized value to submit/save
 * It also formats the REDCap value for display (without altering the stored rc_value).
 */
class NormalizationValidator extends AbstractValidator
{
    /** @var Project */
    private $project;
    /** @var TransformerRegistry */
    private $registry;

    public function __construct(Project $project, TransformerRegistry $registry)
    {
        $this->project = $project;
        $this->registry = $registry;
    }

    public function validate($context)
    {
        /** @var FieldDataDTO $dto */
        $dto = $this->validatedData;
        $rcField = $context['rcField'] ?? null;
        if (!$rcField) return $this; // nothing to do

        $valType = $this->project->metadata[$rcField]['element_validation_type'] ?? '';
        if (!$valType) return $this;

        $transformer = $this->registry->getFor($valType);
        if (!$transformer) return $this;

        // Normalize source values
        foreach ($dto->getSrcValues() as $srcValueDto) {
            $raw = (string)$srcValueDto->getSrcValue();
            if ($raw === '') continue;
            // Ensure raw value is preserved for UI when different from display
            if ($srcValueDto->getRawValue() === null || $srcValueDto->getRawValue() === '') {
                $srcValueDto->setRawValue($raw);
            }
            $normalized = $transformer->normalize($raw, $valType);
            if ($normalized) {
                $srcValueDto->setSrcValue($normalized['save']);    // save-ready value
                $srcValueDto->setDisplay($normalized['display']);  // UI display value
            }
        }

        // Normalize REDCap value for display (do not alter underlying stored value)
        $rcVal = $dto->getRcValue();
        if ($rcVal !== null && $rcVal !== '') {
            $normalizedRc = $transformer->normalize((string)$rcVal, $valType);
            if ($normalizedRc) {
                $dto->setDisplay($normalizedRc['display']);
            }
        }

        return $this;
    }
}
