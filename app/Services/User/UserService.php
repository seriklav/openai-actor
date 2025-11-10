<?php

namespace App\Services\User;

use App\Data\User\UserAuthData;
use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    public function getOrCreate(UserAuthData $dto): User
    {
        return User::query()->firstOrCreate(
            ['email' => $dto->email],
            ['password' => bcrypt(Str::random(40))]
        );
    }
}
