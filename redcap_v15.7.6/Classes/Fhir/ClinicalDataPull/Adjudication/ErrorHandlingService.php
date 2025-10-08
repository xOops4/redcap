<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Adjudication;

use \DynamicDataPull;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\ValueObjects\FetchContextMetadata;

use Exception;
use Language;

class ErrorHandlingService
{
    /** @var Exception|null */
    private $lastError;

    /**
     * Inspect the fetched data and set an appropriate user-facing message when
     * nothing is available for adjudication.
     */
    public function checkForErrors($data_array_src, ?FetchContextMetadata $metadata = null)
    {
        if ($data_array_src === false || empty($data_array_src)) {
            $this->lastError = $this->buildException($metadata);
            return false;
        }

        $this->lastError = null;
        return true;
    }

    public function getLastError(): ?Exception
    {
        return $this->lastError;
    }

    public function renderErrorMessage($message)
    {
        // Generate HTML for displaying error messages to the user
        return '<div class="error">' . htmlspecialchars($message) . '</div>';
    }

    private function buildException(?FetchContextMetadata $metadata): Exception
    {
        $lang = Language::getLanguage();
        $defaultMessage = $lang['ddp_error_fetch_default']; // No data returned for this record!'
        $defaultCode = 0;
        if (!$metadata instanceof FetchContextMetadata) {
            return new Exception($defaultMessage, $defaultCode);
        }

        if (!$metadata->isForceRequested()) {
            if (!$metadata->hasCachedData()) {
                $message = $lang['ddp_error_fetch_cache_hint']; // No cached data is available for this record. Use Force Fetch to request the latest data from the EHR if needed.'
                return new Exception(
                    $message,
                    204
                );
            }
            return new Exception($defaultMessage, $defaultCode);
        }

        $isFhir = strcasecmp($metadata->getWebserviceType(), DynamicDataPull::WEBSERVICE_TYPE_FHIR) === 0;
        if ($isFhir && $metadata->isAccessTokenMissing()) {
            $message = $lang['ddp_error_fetch_token_missing']; // Unable to fetch data because no valid FHIR access token is available. Please complete a standalone or EHR launch to restore access.'
            return new Exception(
                $message,
                401
            );
        }

        $noCacheBefore = !$metadata->hadCachedData();
        $noCacheAfter = !$metadata->hasCachedData();
        $noErrors = empty($metadata->getErrors());
        if ($metadata->wasFetchAttempted() && $noCacheBefore && $noCacheAfter && $noErrors) {
            $message = $lang['ddp_error_fetch_empty_force']; // There was no existing cache for this record and nothing new was returned from the external source.'
            return new Exception(
                $message,
                204
            );
        }

        return new Exception($defaultMessage, $defaultCode);
    }
}
