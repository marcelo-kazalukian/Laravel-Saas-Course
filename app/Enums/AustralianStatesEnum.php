<?php

namespace App\Enums;

enum AustralianStatesEnum: string
{
    case NSW = 'NSW';
    case VIC = 'VIC';
    case QLD = 'QLD';
    case WA = 'WA';
    case SA = 'SA';
    case TAS = 'TAS';
    case ACT = 'ACT';
    case NT = 'NT';

    public function label(): string
    {
        return match ($this) {
            self::NSW => 'New South Wales',
            self::VIC => 'Victoria',
            self::QLD => 'Queensland',
            self::WA => 'Western Australia',
            self::SA => 'South Australia',
            self::TAS => 'Tasmania',
            self::ACT => 'Australian Capital Territory',
            self::NT => 'Northern Territory',
        };
    }
}
