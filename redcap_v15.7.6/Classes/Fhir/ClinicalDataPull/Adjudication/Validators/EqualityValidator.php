<?php
namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Validators;

use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Utilities\TextNormalizer;

class EqualityValidator extends AbstractValidator
{

    public function validate($context)
    {
        $srcValues = $this->validatedData->getSrcValues();
        $rcValue = $this->validatedData->getRcValue();
        $rcDisplay = $this->validatedData->getDisplay();
        $rcComparable = ($rcDisplay !== null && $rcDisplay !== '') ? $rcDisplay : $rcValue;
        foreach ($srcValues as $srcValue) {
            $srcDisplay = $srcValue->getDisplay();
            $srcComparable = ($srcDisplay !== null && $srcDisplay !== '') ? $srcDisplay : $srcValue->getSrcValue();
            $normalizedSrc = TextNormalizer::normalizeText($srcComparable);
            $normalizedRc  = TextNormalizer::normalizeText($rcComparable);
            $srcValue->setIsEqual($normalizedSrc === $normalizedRc);
        }

        return $this;
    }
}
