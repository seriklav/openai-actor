<?php

namespace App\Data\User;

use Spatie\LaravelData\Data;

class UserAuthData extends Data
{
    public function __construct(
        public string $email,
        public ?string $name = null,
    ) {}
}
