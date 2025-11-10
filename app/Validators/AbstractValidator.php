<?php

namespace App\Validators;

use Illuminate\Validation\Validator;

abstract class AbstractValidator
{
	public abstract function validate(Validator $validator): void;
}
