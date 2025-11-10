<?php

namespace Tests\Unit\Repositories;

use App\Data\Actor\ActorData;
use App\Models\Actor;
use App\Models\User;
use App\Repositories\Actor\ActorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ActorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ActorRepository();
    }

    public function test_creates_new_actor_with_valid_data(): void
    {
        $user = User::factory()->create();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = fake()->sentence();

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            gender: 'male',
            description: $description,
            height: 180,
            weight: 75,
            age: 25
        );

        $actor = $this->repository->create($user, $actorData);

        $this->assertInstanceOf(Actor::class, $actor);
        $this->assertEquals($user->id, $actor->user_id);
        $this->assertEquals($firstName, $actor->first_name);
        $this->assertEquals($lastName, $actor->last_name);
        $this->assertEquals($address, $actor->address);
        $this->assertEquals('male', $actor->gender->value);
        $this->assertEquals($description, $actor->description);
        $this->assertEquals(180, $actor->height);
        $this->assertEquals(75, $actor->weight);
        $this->assertEquals(25, $actor->age);
        $this->assertDatabaseHas('actors', [
            'user_id' => $user->id,
            'first_name' => $firstName,
        ]);
    }

    public function test_first_or_creates_actor_when_duplicate_exists(): void
    {
        $user = User::factory()->create();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = fake()->sentence();

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            gender: 'female',
            description: $description,
            height: 165,
            weight: 60,
            age: 30
        );

        $firstActor = $this->repository->create($user, $actorData);
        $secondActor = $this->repository->create($user, $actorData);

        $this->assertEquals($firstActor->id, $secondActor->id);
        $this->assertEquals(1, Actor::count());
    }

    public function test_checks_if_actors_exist_by_data(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Actor',
            'description' => 'unique description',
        ]);

        $existingData = new ActorData(
            userId: $user->id,
            description: 'unique description'
        );

        $nonExistingData = new ActorData(
            userId: $user->id,
            description: 'non-existent description'
        );

        $this->assertTrue($this->repository->hasActorsByData($existingData));
        $this->assertFalse($this->repository->hasActorsByData($nonExistingData));
    }

    public function test_gets_actors_by_user_id(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Actor::factory()->count(3)->create(['user_id' => $user1->id]);
        Actor::factory()->count(2)->create(['user_id' => $user2->id]);

        $data = new ActorData(userId: $user1->id, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(3, $result->items());
        foreach ($result->items() as $actor) {
            $this->assertEquals($user1->id, $actor->user_id);
        }
    }

    public function test_filters_actors_by_first_name(): void
    {
        $user = User::factory()->create();
        $searchName = fake()->firstName();

        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => $searchName . 'ander',
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => $searchName . 'andra',
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => fake()->firstName(),
        ]);

        $data = new ActorData(firstName: $searchName, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_last_name(): void
    {
        $user = User::factory()->create();
        $searchName = fake()->lastName();

        Actor::factory()->create(['user_id' => $user->id, 'last_name' => $searchName]);
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => $searchName . 'son']);
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => fake()->lastName()]);

        $data = new ActorData(lastName: $searchName, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_address(): void
    {
        $user = User::factory()->create();
        $searchAddress = fake()->country();

        Actor::factory()->create(['user_id' => $user->id, 'address' => $searchAddress . ' York']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => $searchAddress . ' Jersey']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => fake()->city()]);

        $data = new ActorData(address: $searchAddress, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_gender(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'male']);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'male']);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'female']);

        $data = new ActorData(gender: 'male', perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_height(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'height' => 180]);
        Actor::factory()->create(['user_id' => $user->id, 'height' => 180]);
        Actor::factory()->create(['user_id' => $user->id, 'height' => 170]);

        $data = new ActorData(height: 180, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_weight(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 75]);
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 80]);
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 75]);

        $data = new ActorData(weight: 75, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_age(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'age' => 25]);
        Actor::factory()->create(['user_id' => $user->id, 'age' => 30]);
        Actor::factory()->create(['user_id' => $user->id, 'age' => 25]);

        $data = new ActorData(age: 25, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_description(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Experienced actor']);
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Experienced director']);
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Newcomer']);

        $data = new ActorData(description: 'Experienced', perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(2, $result->items());
    }

    public function test_paginates_actors_correctly(): void
    {
        $user = User::factory()->create();
        Actor::factory()->count(25)->create(['user_id' => $user->id]);

        $dataPage1 = new ActorData(userId: $user->id, perPage: 10, page: 1);
        $dataPage2 = new ActorData(userId: $user->id, perPage: 10, page: 2);

        $resultPage1 = $this->repository->getActorsByData($dataPage1);
        $resultPage2 = $this->repository->getActorsByData($dataPage2);

        $this->assertCount(10, $resultPage1->items());
        $this->assertCount(10, $resultPage2->items());
        $this->assertEquals(25, $resultPage1->total());
        $this->assertEquals(25, $resultPage2->total());
    }

    public function test_orders_actors_by_latest(): void
    {
        $user = User::factory()->create();
        $oldActor = Actor::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);
        $newActor = Actor::factory()->create([
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $data = new ActorData(userId: $user->id, perPage: 10);

        $result = $this->repository->getActorsByData($data);

        $this->assertEquals($newActor->id, $result->items()[0]->id);
        $this->assertEquals($oldActor->id, $result->items()[1]->id);
    }

    public function test_combines_multiple_filters(): void
    {
        $user = User::factory()->create();
        $firstName = fake()->firstName();

        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'gender' => 'male',
            'age' => 25,
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'gender' => 'male',
            'age' => 30,
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => fake()->firstName(),
            'gender' => 'female',
            'age' => 25,
        ]);

        $data = new ActorData(
            userId: $user->id,
            firstName: $firstName,
            gender: 'male',
            age: 25,
            perPage: 10
        );

        $result = $this->repository->getActorsByData($data);

        $this->assertCount(1, $result->items());
        $this->assertEquals($firstName, $result->items()[0]->first_name);
        $this->assertEquals(25, $result->items()[0]->age);
    }
}
