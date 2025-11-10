<?php

namespace App\Data\Actor;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ActorData extends Data
{
    public function __construct(
        #[MapInputName('user_id')]
        #[MapOutputName('user_id')]
        public Optional|null|int $userId = null,
        #[MapInputName('first_name')]
        #[MapOutputName('first_name')]
        public Optional|null|string $firstName = null,
        #[MapInputName('last_name')]
        #[MapOutputName('last_name')]
        public Optional|null|string $lastName = null,
        public Optional|null|string $address = null,
        public Optional|null|string $gender = null,
        public Optional|null|string $description = null,
        public Optional|null|int $height = null,
        public Optional|null|int $weight = null,
        public Optional|null|int $age = null,
        public Optional|null|int $page = null,
        #[MapInputName('per_page')]
        public Optional|null|int $perPage = null,
    ) {}
}
