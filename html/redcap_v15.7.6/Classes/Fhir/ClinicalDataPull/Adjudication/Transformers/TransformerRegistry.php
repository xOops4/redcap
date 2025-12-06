<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication\Transformers;

/**
 * Simple registry for mapping validation types to transformers.
 *
 * Usage
 *   $r = new TransformerRegistry();
 *   $r->register(new DateTimeTransformer());
 *   $t = $r->getFor('date_ymd'); // DateTimeTransformer
 */
class TransformerRegistry
{
    /** @var TransformerInterface[] */
    private $transformers = [];

    public function register(TransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
    }

    public function getFor(string $validationType): ?TransformerInterface
    {
        foreach ($this->transformers as $t) {
            if ($t->supports($validationType)) return $t;
        }
        return null;
    }
}
