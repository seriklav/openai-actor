<?php

declare(strict_types=1);

namespace App\Exceptions\OpenAI;

use RuntimeException;

class InvalidOpenAiResponseException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('exceptions.openai_invalid_response'));
    }
}
