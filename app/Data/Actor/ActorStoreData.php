<?php

namespace App\Data\Actor;

use Spatie\LaravelData\Data;

class ActorStoreData extends Data
{
    public function __construct(
        public string $email,
        public string $description,
    ) {}
}
