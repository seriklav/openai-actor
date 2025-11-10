<?php

declare(strict_types=1);

namespace App\Exceptions\Actor;

use RuntimeException;

class ActorAddressMissing extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('exceptions.actor_address_missing'));
    }
}
