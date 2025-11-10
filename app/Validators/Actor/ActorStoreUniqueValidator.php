<?php

declare(strict_types=1);

namespace App\Validators\Actor;

use App\Data\Actor\ActorData;
use App\Data\User\UserAuthData;
use App\Repositories\Actor\ActorRepository;
use App\Services\User\UserService;
use App\Validators\AbstractValidator;
use Illuminate\Validation\Validator;

class ActorStoreUniqueValidator extends AbstractValidator
{
    public function __construct(
        protected UserService $userService,
        protected ActorRepository $actorRepository
    ){
    }

    public function validate(Validator $validator): void
	{
		$email = request()->input('email');
		$description = request()->input('description');

        if (empty($email) || empty($description)) {
            return;
        }

        $user = $this->userService->getOrCreate(
            new UserAuthData(email: $email)
        );

        if (
            $this->actorRepository->hasActorsByData(
                new ActorData(
                    userId: $user->id,
                    description: $description
                )
            )
        ) {
            $validator->errors()->add(
                'quantity',
                __('validation.custom.actor.unique_user_description')
            );
        }
	}
}
