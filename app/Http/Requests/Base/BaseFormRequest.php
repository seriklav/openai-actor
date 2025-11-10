<?php

namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Data;

/**
 * @template TDto of Data
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * @return class-string<TDto>
     */
    abstract protected function dtoClass(): string;

    /**
     * @return TDto
     */
    public function getDto(): Data
    {
        $dtoClass = $this->dtoClass();

        $data = $this->validated();

        foreach ($this->allFiles() as $key => $file) {
            if ($file instanceof UploadedFile) {
                $data[$key] = $file;
            }
        }

        if (array_is_list($data)) {
            $data = ['items' => $data];
        }

        /** @var TDto $dto */
        return $dtoClass::from($data);
    }

    protected function validators(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        foreach ($this->validators() as $customValidator) {
            $validator->after(fn(Validator $validator) => app($customValidator)
                ->validate($validator)
            );
        }
    }
}
