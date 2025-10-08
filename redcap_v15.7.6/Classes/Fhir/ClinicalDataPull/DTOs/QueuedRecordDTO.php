<?php
namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\DTOs;

use Vanderbilt\REDCap\Classes\DTOs\DTO;

class QueuedRecordDTO extends DTO {
    use RecordLinkTrait;
    
    /** @var int|string */
    public $record;
    /** @var int|string */
    public $fetch_status;
    /** @var int|string */
    public $updated_at;
}