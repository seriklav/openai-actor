<?php

declare(strict_types=1);

namespace App\Enums\Actor;

enum GenderEnum: string
{
    case MALE   = 'male';
    case FEMALE = 'female';
    case OTHER  = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MALE   => __('actor.gender.male'),
            self::FEMALE => __('actor.gender.female'),
            self::OTHER  => __('actor.gender.other'),
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $g) => $g->value, self::cases());
    }
}
