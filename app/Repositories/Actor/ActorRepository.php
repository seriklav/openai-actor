<?php

namespace App\Repositories\Actor;

use App\Data\Actor\ActorData;
use App\Models\Actor;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActorRepository
{
    protected function getBuilder(ActorData $data): Builder
    {
        return Actor::query()
            ->when($data->userId, fn(Builder $q) => $q->where('user_id', $data->userId))
            ->when($data->firstName, fn(Builder $q) => $q->where('first_name', 'LIKE', "%{$data->firstName}%"))
            ->when($data->lastName, fn(Builder $q) => $q->where('last_name', 'LIKE', "%{$data->lastName}%"))
            ->when($data->address, fn(Builder $q) => $q->where('address', 'LIKE', "%{$data->address}%"))
            ->when($data->gender, fn(Builder $q) => $q->where('gender', $data->gender))
            ->when($data->description, fn(Builder $q) => $q->where('description', 'LIKE', "%{$data->description}%"))
            ->when($data->height, fn(Builder $q) => $q->where('height', $data->height))
            ->when($data->weight, fn(Builder $q) => $q->where('weight', $data->weight))
            ->when($data->age, fn(Builder $q) => $q->where('age', $data->age));
    }

    public function hasActorsByData(ActorData $data): bool
    {
        return $this->getBuilder($data)->exists();
    }

    public function getActorsByData(ActorData $data): Collection|LengthAwarePaginator
    {
        $query = $this
            ->getBuilder($data)
            ->latest();

        return $query->paginate(
            perPage: $data->perPage,
            page: $data->page ?? null,
        );
    }

    public function create(User $user, ActorData $data): Actor
    {
        $data = [
            ...['user_id' => $user->id],
            ...$data->except('userId', 'page', 'perPage')->toArray()
        ];

        return Actor::query()->firstOrCreate($data);
    }
}
