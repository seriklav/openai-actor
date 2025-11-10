<?php

declare(strict_types=1);

namespace App\Exceptions\Actor;

use RuntimeException;

class ActorFirstNameMissing extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('exceptions.actor_first_name_missing'));
    }
}
