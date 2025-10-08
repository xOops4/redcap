<?php

namespace Vanderbilt\REDCap\Classes\Validation;

/**
 * Central list of REDCap validation type constants used across the codebase.
 */
class ValidationTypes
{
    // Date-only
    public const DATE_YMD = 'date_ymd';
    public const DATE_MDY = 'date_mdy';
    public const DATE_DMY = 'date_dmy';

    // Datetime without seconds
    public const DATETIME_YMD = 'datetime_ymd';
    public const DATETIME_MDY = 'datetime_mdy';
    public const DATETIME_DMY = 'datetime_dmy';

    // Datetime with seconds
    public const DATETIME_SECONDS_YMD = 'datetime_seconds_ymd';
    public const DATETIME_SECONDS_MDY = 'datetime_seconds_mdy';
    public const DATETIME_SECONDS_DMY = 'datetime_seconds_dmy';

    // Common non-date types
    public const FLOAT = 'float';
    public const INT = 'int';
    public const EMAIL = 'email';
    public const PHONE = 'phone';
    public const TIME = 'time';
    public const ZIPCODE = 'zipcode';
    public const VMRN = 'vmrn';
    public const NUMBER_2DP = 'number_2dp';
    public const JHEID_E9D = 'jheid_e9d';
    public const ALPHA_ONLY = 'alpha_only';
    public const NUMBER = 'number';
    public const PHONE800 = 'phone800';
    public const SIGNATURE = 'signature';
    public const SSN = 'ssn';
    public const AUTOCOMPLETE = 'autocomplete';
    public const TIME_HH_MM_SS = 'time_hh_mm_ss';
    public const NUMBER_1DP = 'number_1dp';
    public const JHMRN_JH8D = 'jhmrn_jh8d';

    /**
     * Convenience groups used by adjudication
     */
    public const DATE_TYPES = [
        self::DATE_YMD,
        self::DATE_MDY,
        self::DATE_DMY,
        self::DATETIME_YMD,
        self::DATETIME_MDY,
        self::DATETIME_DMY,
        self::DATETIME_SECONDS_YMD,
        self::DATETIME_SECONDS_MDY,
        self::DATETIME_SECONDS_DMY,
    ];

    public const PHONE_TYPES = [
        self::PHONE,
        self::PHONE800,
    ];
}

