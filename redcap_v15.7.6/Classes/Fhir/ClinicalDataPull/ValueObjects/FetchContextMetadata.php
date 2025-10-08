<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\ValueObjects;

use Throwable;

/**
 * Value Object recording the outcome of a data-fetch operation and the state of
 * the cache before/after the attempt. It enables higher layers to present
 * nuanced feedback without re-inspecting low-level services.
 *
 * Key concepts captured:
 * - forceRequested/forceApplied: was a forced fetch asked for and executed?
 * - webserviceType: which integration handled the call (FHIR, CUSTOM, ...).
 * - fetchAttempted: did we actually reach out to the remote service?
 * - cache state: whether cached data existed before/after and if it changed.
 * - errors/accessTokenMissing: surfaced exceptions and common authorization
 *   shortcut flag for FHIR token issues.
 *
 * To extend scenarios, add new setters/predicates here and keep consumer logic
 * focused on the semantics instead of raw arrays.
 */
class FetchContextMetadata
{

    /** @var bool */
    private $forceRequested = false;
    /** @var bool */
    private $forceApplied = false;
    /** @var string */
    private $webserviceType = '';
    /** @var bool */
    private $fetchAttempted = false;
    /** @var bool */
    private $hadCachedData = false;
    /** @var bool */
    private $hasCachedData = false;
    /** @var bool */
    private $cacheChanged = false;
    /** @var array<int, Throwable> */
    private $errors = [];
    /** @var bool */
    private $accessTokenMissing = false;

    public static function create(): self
    {
        return new self();
    }

    /**
     * Record the force-fetch context as seen by the caller and the service.
     */
    public function setForceContext(bool $forceRequested, bool $forceApplied): self
    {
        $this->forceRequested = $forceRequested;
        $this->forceApplied = $forceApplied;
        return $this;
    }

    /**
     * Capture the integration type responsible for the fetch attempt.
     */
    public function setWebserviceType(?string $webserviceType): self
    {
        $this->webserviceType = (string) $webserviceType;
        return $this;
    }

    /**
     * Flag whether a remote call was attempted in this lifecycle.
     */
    public function markFetchAttempted(bool $attempted = true): self
    {
        $this->fetchAttempted = $attempted;
        return $this;
    }

    /**
     * Persist cache state before/after the fetch so dependent logic can
     * reason about what changed.
     */
    public function setCacheState(bool $hadCachedData, bool $hasCachedData, bool $cacheChanged): self
    {
        $this->hadCachedData = $hadCachedData;
        $this->hasCachedData = $hasCachedData;
        $this->cacheChanged = $cacheChanged;
        return $this;
    }

    /**
     * @param iterable<int, Throwable> $errors
     */
    public function addErrors(iterable $errors): self
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
        return $this;
    }

    public function addError(Throwable $error): self
    {
        $this->errors[] = $error;
        if ($error->getCode() === 401) {
            $this->accessTokenMissing = true;
        }
        return $this;
    }

    public function isForceRequested(): bool
    {
        return $this->forceRequested;
    }

    public function isForceApplied(): bool
    {
        return $this->forceApplied;
    }

    public function getWebserviceType(): string
    {
        return $this->webserviceType;
    }

    public function wasFetchAttempted(): bool
    {
        return $this->fetchAttempted;
    }

    public function hadCachedData(): bool
    {
        return $this->hadCachedData;
    }

    public function hasCachedData(): bool
    {
        return $this->hasCachedData;
    }

    public function wasCacheChanged(): bool
    {
        return $this->cacheChanged;
    }

    /**
     * @return array<int, Throwable>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Convenience indicator for the common FHIR token scenario.
     */
    public function isAccessTokenMissing(): bool
    {
        return $this->accessTokenMissing;
    }

    /**
     * Provide a serializable snapshot for logging or debugging.
     */
    public function toArray(): array
    {
        return [
            'forceRequested' => $this->forceRequested,
            'forceApplied' => $this->forceApplied,
            'webserviceType' => $this->webserviceType,
            'fetchAttempted' => $this->fetchAttempted,
            'hadCachedData' => $this->hadCachedData,
            'hasCachedData' => $this->hasCachedData,
            'cacheChanged' => $this->cacheChanged,
            'errors' => $this->errors,
            'accessTokenMissing' => $this->accessTokenMissing,
        ];
    }
}
