<?php

namespace App\Http\Requests\Actor;

use App\Data\Actor\ActorData;
use App\Http\Requests\Base\BaseFormRequest;

class ActorIndexRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'nullable', 'string'],
            'last_name' => ['sometimes', 'nullable', 'string'],
            'address' => ['sometimes', 'nullable', 'string'],
            'gender' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'height' => ['sometimes', 'nullable', 'integer'],
            'weight' => ['sometimes', 'nullable', 'integer'],
            'age' => ['sometimes', 'nullable', 'integer'],
            'page' => ['sometimes', 'nullable', 'integer'],
            'per_page' => ['sometimes', 'nullable', 'integer'],
        ];
    }

    protected function dtoClass(): string
    {
        return ActorData::class;
    }
}
