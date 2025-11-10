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
        // Arrange
        $user = User::factory()->create();
        $actorData = new ActorData(
            firstName: 'John',
            lastName: 'Doe',
            address: 'New York',
            gender: 'male',
            description: 'Test description',
            height: 180,
            weight: 75,
            age: 25
        );

        // Act
        $actor = $this->repository->create($user, $actorData);

        // Assert
        $this->assertInstanceOf(Actor::class, $actor);
        $this->assertEquals($user->id, $actor->user_id);
        $this->assertEquals('John', $actor->first_name);
        $this->assertEquals('Doe', $actor->last_name);
        $this->assertEquals('New York', $actor->address);
        $this->assertEquals('male', $actor->gender->value);
        $this->assertEquals('Test description', $actor->description);
        $this->assertEquals(180, $actor->height);
        $this->assertEquals(75, $actor->weight);
        $this->assertEquals(25, $actor->age);
        $this->assertDatabaseHas('actors', [
            'user_id' => $user->id,
            'first_name' => 'John',
        ]);
    }

    public function test_first_or_creates_actor_when_duplicate_exists(): void
    {
        // Arrange
        $user = User::factory()->create();
        $actorData = new ActorData(
            firstName: 'Jane',
            lastName: 'Smith',
            address: 'London',
            gender: 'female',
            description: 'Duplicate test',
            height: 165,
            weight: 60,
            age: 30
        );

        // Act
        $firstActor = $this->repository->create($user, $actorData);
        $secondActor = $this->repository->create($user, $actorData);

        // Assert
        $this->assertEquals($firstActor->id, $secondActor->id);
        $this->assertEquals(1, Actor::count());
    }

    public function test_checks_if_actors_exist_by_data(): void
    {
        // Arrange
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

        // Act & Assert
        $this->assertTrue($this->repository->hasActorsByData($existingData));
        $this->assertFalse($this->repository->hasActorsByData($nonExistingData));
    }

    public function test_gets_actors_by_user_id(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Actor::factory()->count(3)->create(['user_id' => $user1->id]);
        Actor::factory()->count(2)->create(['user_id' => $user2->id]);

        $data = new ActorData(userId: $user1->id, perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(3, $result->items());
        foreach ($result->items() as $actor) {
            $this->assertEquals($user1->id, $actor->user_id);
        }
    }

    public function test_filters_actors_by_first_name(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Alexander',
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Alexandra',
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
        ]);

        $data = new ActorData(firstName: 'Alex', perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_last_name(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => 'Smith']);
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => 'Smithson']);
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => 'Johnson']);

        $data = new ActorData(lastName: 'Smith', perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'address' => 'New York']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => 'New Jersey']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => 'Los Angeles']);

        $data = new ActorData(address: 'New', perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_gender(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'male']);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'male']);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'female']);

        $data = new ActorData(gender: 'male', perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_height(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'height' => 180]);
        Actor::factory()->create(['user_id' => $user->id, 'height' => 180]);
        Actor::factory()->create(['user_id' => $user->id, 'height' => 170]);

        $data = new ActorData(height: 180, perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_weight(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 75]);
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 80]);
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 75]);

        $data = new ActorData(weight: 75, perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_age(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'age' => 25]);
        Actor::factory()->create(['user_id' => $user->id, 'age' => 30]);
        Actor::factory()->create(['user_id' => $user->id, 'age' => 25]);

        $data = new ActorData(age: 25, perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_filters_actors_by_description(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Experienced actor']);
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Experienced director']);
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Newcomer']);

        $data = new ActorData(description: 'Experienced', perPage: 10);

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(2, $result->items());
    }

    public function test_paginates_actors_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->count(25)->create(['user_id' => $user->id]);

        $dataPage1 = new ActorData(userId: $user->id, perPage: 10, page: 1);
        $dataPage2 = new ActorData(userId: $user->id, perPage: 10, page: 2);

        // Act
        $resultPage1 = $this->repository->getActorsByData($dataPage1);
        $resultPage2 = $this->repository->getActorsByData($dataPage2);

        // Assert
        $this->assertCount(10, $resultPage1->items());
        $this->assertCount(10, $resultPage2->items());
        $this->assertEquals(25, $resultPage1->total());
        $this->assertEquals(25, $resultPage2->total());
    }

    public function test_orders_actors_by_latest(): void
    {
        // Arrange
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

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertEquals($newActor->id, $result->items()[0]->id);
        $this->assertEquals($oldActor->id, $result->items()[1]->id);
    }

    public function test_combines_multiple_filters(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'gender' => 'male',
            'age' => 25,
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'gender' => 'male',
            'age' => 30,
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'gender' => 'female',
            'age' => 25,
        ]);

        $data = new ActorData(
            userId: $user->id,
            firstName: 'John',
            gender: 'male',
            age: 25,
            perPage: 10
        );

        // Act
        $result = $this->repository->getActorsByData($data);

        // Assert
        $this->assertCount(1, $result->items());
        $this->assertEquals('John', $result->items()[0]->first_name);
        $this->assertEquals(25, $result->items()[0]->age);
    }
}
