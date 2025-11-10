<?php

namespace App\Services\Actor;

use App\Data\Actor\ActorData;
use App\Data\Actor\ActorStoreData;
use App\Data\User\UserAuthData;
use App\Models\Actor;
use App\Repositories\Actor\ActorRepository;
use App\Services\OpenAi\OpenAiService;
use App\Services\User\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ActorService
{
    public function __construct(
        protected UserService $userService,
        protected OpenAiService $openAiService,
        protected ActorRepository $actorRepository,
    ) {}

    public function store(ActorStoreData $dto): Actor
    {
        $user = $this->userService->getOrCreate(
            new UserAuthData(email: $dto->email)
        );

        try {
            $parsed = $this->openAiService->getActorData($dto->description);

            auth()->login($user);

            return $this->actorRepository->create($user, $parsed);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'description' => $e->getMessage(),
            ]);
        }
    }

    public function getList(ActorData $data): LengthAwarePaginator
    {
        return $this->actorRepository->getActorsByData($data);
    }
}
