<?php

declare(strict_types=1);

namespace App\Exceptions\Actor;

use RuntimeException;

class ActorLastNameMissing extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('exceptions.actor_last_name_missing'));
    }
}
