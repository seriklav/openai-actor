<?php

namespace Tests\Unit\Services;

use App\Data\Actor\ActorData;
use App\Data\Actor\ActorStoreData;
use App\Data\User\UserAuthData;
use App\Enums\Actor\GenderEnum;
use App\Exceptions\Actor\ActorFirstNameMissing;
use App\Exceptions\OpenAI\InvalidOpenAiResponseException;
use App\Models\Actor;
use App\Models\User;
use App\Repositories\Actor\ActorRepository;
use App\Services\Actor\ActorService;
use App\Services\OpenAi\OpenAiService;
use App\Services\User\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ActorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActorService $service;
    protected UserService $userService;
    protected OpenAiService $openAiService;
    protected ActorRepository $actorRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = Mockery::mock(UserService::class);
        $this->openAiService = Mockery::mock(OpenAiService::class);
        $this->actorRepository = Mockery::mock(ActorRepository::class);

        $this->service = new ActorService(
            $this->userService,
            $this->openAiService,
            $this->actorRepository
        );
    }

    public function test_successfully_stores_actor_with_valid_data(): void
    {
        $email = fake()->email();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();

        $dto = new ActorStoreData(
            email: $email,
            description: fake()->sentence()
        );

        $user = User::factory()->make(['id' => 1, 'email' => $email]);
        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            gender: GenderEnum::MALE->value,
            description: $dto->description,
            height: 180,
            weight: 75,
            age: 25
        );
        $actor = Actor::factory()->make(['id' => 1]);

        $this->userService
            ->shouldReceive('getOrCreate')
            ->once()
            ->with(Mockery::type(UserAuthData::class))
            ->andReturn($user);

        $this->openAiService
            ->shouldReceive('getActorData')
            ->once()
            ->with($dto->description)
            ->andReturn($actorData);

        $this->actorRepository
            ->shouldReceive('create')
            ->once()
            ->with($user, $actorData)
            ->andReturn($actor);

        $result = $this->service->store($dto);

        $this->assertInstanceOf(Actor::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    public function test_throws_validation_exception_when_openai_service_fails(): void
    {
        $email = fake()->email();
        $dto = new ActorStoreData(
            email: $email,
            description: fake()->sentence()
        );

        $user = User::factory()->make(['id' => 1, 'email' => $email]);

        $this->userService
            ->shouldReceive('getOrCreate')
            ->once()
            ->andReturn($user);

        $this->openAiService
            ->shouldReceive('getActorData')
            ->once()
            ->andThrow(new InvalidOpenAiResponseException());

        $this->expectException(ValidationException::class);

        $this->service->store($dto);
    }

    public function test_validation_exception_contains_proper_error_message(): void
    {
        $dto = new ActorStoreData(
            email: fake()->email(),
            description: fake()->sentence()
        );

        $user = User::factory()->make(['id' => 1]);

        $this->userService
            ->shouldReceive('getOrCreate')
            ->once()
            ->andReturn($user);

        $this->openAiService
            ->shouldReceive('getActorData')
            ->once()
            ->andThrow(new ActorFirstNameMissing());

        try {
            $this->service->store($dto);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('description', $e->errors());
            $this->assertNotEmpty($e->errors()['description']);
        }
    }

    public function test_logs_in_user_during_store_process(): void
    {
        $email = fake()->email();
        $dto = new ActorStoreData(
            email: $email,
            description: fake()->sentence()
        );

        $user = User::factory()->create(['email' => $email]);
        $actorData = new ActorData(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            address: fake()->city(),
            description: $dto->description
        );
        $actor = Actor::factory()->make();

        $this->userService
            ->shouldReceive('getOrCreate')
            ->once()
            ->andReturn($user);

        $this->openAiService
            ->shouldReceive('getActorData')
            ->once()
            ->andReturn($actorData);

        $this->actorRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($actor);

        $this->assertFalse(auth()->check());

        $this->service->store($dto);

        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    public function test_get_list_returns_paginated_actors(): void
    {
        $actorData = new ActorData(
            userId: 1,
            page: 1,
            perPage: 10
        );

        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $this->actorRepository
            ->shouldReceive('getActorsByData')
            ->once()
            ->with(Mockery::on(
                fn ($arg) =>
                    $arg instanceof ActorData
                    && $arg->userId === $actorData->userId
                    && $arg->perPage === $actorData->perPage
                    && $arg->page === $actorData->page
            ))
            ->andReturn($paginatorMock);

        $result = $this->service->getList($actorData);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_get_list_with_filters(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        $actorData = new ActorData(
            userId: 1,
            firstName: $firstName,
            lastName: $lastName,
            gender: GenderEnum::MALE->value,
            age: 25,
            perPage: 10
        );

        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $this->actorRepository
            ->shouldReceive('getActorsByData')
            ->once()
            ->with(Mockery::on(function ($arg) use ($firstName, $lastName) {
                return $arg instanceof ActorData
                    && $arg->firstName === $firstName
                    && $arg->lastName === $lastName
                    && $arg->gender === GenderEnum::MALE->value
                    && $arg->age === 25;
            }))
            ->andReturn($paginatorMock);

        $result = $this->service->getList($actorData);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_store_passes_correct_user_auth_data_to_user_service(): void
    {
        $email = fake()->email();
        $dto = new ActorStoreData(
            email: $email,
            description: fake()->sentence()
        );

        $user = User::factory()->make(['email' => $email]);
        $actorData = new ActorData(
            firstName: fake()->firstName(),
            lastName: fake()->lastName(),
            address: fake()->city(),
            description: $dto->description
        );
        $actor = Actor::factory()->make();

        $this->userService
            ->shouldReceive('getOrCreate')
            ->once()
            ->with(Mockery::on(function ($arg) use ($email) {
                return $arg instanceof UserAuthData && $arg->email === $email;
            }))
            ->andReturn($user);

        $this->openAiService
            ->shouldReceive('getActorData')
            ->once()
            ->andReturn($actorData);

        $this->actorRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($actor);

        $this->service->store($dto);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
