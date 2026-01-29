<?php

namespace App\Enums;

enum WeekDayEnum: string
{
    case MONDAY = '1';
    case TUESDAY = '2';
    case WEDNESDAY = '3';
    case THURSDAY = '4';
    case FRIDAY = '5';
    case SATURDAY = '6';
    case SUNDAY = '7';

    public function label(): string
    {
        return match ($this) {
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
            self::SUNDAY => 'Sunday',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn ($day) => [
                'value' => $day->value,
                'label' => $day->label(),
            ])
            ->toArray();
    }
}
