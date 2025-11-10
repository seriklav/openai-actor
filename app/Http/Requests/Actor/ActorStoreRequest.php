<?php

namespace App\Http\Requests\Actor;

use App\Data\Actor\ActorStoreData;
use App\Http\Requests\Base\BaseFormRequest;
use App\Validators\Actor\ActorStoreUniqueValidator;

class ActorStoreRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }

    public function validators(): array
    {
        return [
            ActorStoreUniqueValidator::class
        ];
    }

    protected function dtoClass(): string
    {
        return ActorStoreData::class;
    }
}
