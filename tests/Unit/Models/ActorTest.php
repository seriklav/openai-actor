<?php

namespace Tests\Unit\Models;

use App\Enums\Actor\GenderEnum;
use App\Models\Actor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorTest extends TestCase
{
    use RefreshDatabase;

    public function test_actor_belongs_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $actor = Actor::factory()->create(['user_id' => $user->id]);

        // Act
        $actorUser = $actor->user;

        // Assert
        $this->assertInstanceOf(User::class, $actorUser);
        $this->assertEquals($user->id, $actorUser->id);
    }

    public function test_actor_has_fillable_attributes(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'description' => 'Test description',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'New York',
            'height' => 180,
            'weight' => 75,
            'gender' => 'male',
            'age' => 25,
        ];

        // Act
        $actor = Actor::create($data);

        // Assert
        $this->assertEquals($data['user_id'], $actor->user_id);
        $this->assertEquals($data['description'], $actor->description);
        $this->assertEquals($data['first_name'], $actor->first_name);
        $this->assertEquals($data['last_name'], $actor->last_name);
        $this->assertEquals($data['address'], $actor->address);
        $this->assertEquals($data['height'], $actor->height);
        $this->assertEquals($data['weight'], $actor->weight);
        $this->assertEquals($data['gender'], $actor->gender->value);
        $this->assertEquals($data['age'], $actor->age);
    }

    public function test_gender_is_cast_to_enum(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['gender' => 'male']);

        // Assert
        $this->assertInstanceOf(GenderEnum::class, $actor->gender);
        $this->assertEquals('male', $actor->gender->value);
    }

    public function test_actor_can_have_male_gender(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['gender' => 'male']);

        // Assert
        $this->assertEquals(GenderEnum::MALE, $actor->gender);
    }

    public function test_actor_can_have_female_gender(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['gender' => 'female']);

        // Assert
        $this->assertEquals(GenderEnum::FEMALE, $actor->gender);
    }

    public function test_actor_can_have_other_gender(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['gender' => 'other']);

        // Assert
        $this->assertEquals(GenderEnum::OTHER, $actor->gender);
    }

    public function test_actor_has_timestamps(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create();

        // Assert
        $this->assertNotNull($actor->created_at);
        $this->assertNotNull($actor->updated_at);
    }

    public function test_actor_updates_timestamp_on_modification(): void
    {
        // Arrange
        $actor = Actor::factory()->create();
        $oldUpdatedAt = $actor->updated_at;

        sleep(1);

        // Act
        $actor->update(['first_name' => 'Updated Name']);

        // Assert
        $this->assertTrue($actor->updated_at->isAfter($oldUpdatedAt));
    }

    public function test_actor_can_be_created_with_minimal_data(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actor = Actor::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'Test City',
            'gender' => 'male',
        ]);

        // Assert
        $this->assertNotNull($actor->id);
        $this->assertEquals('John', $actor->first_name);
    }

    public function test_actor_can_have_null_optional_fields(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actor = Actor::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address' => 'Test City',
            'gender' => 'female',
            'description' => null,
            'height' => null,
            'weight' => null,
            'age' => null,
        ]);

        // Assert
        $this->assertNull($actor->description);
        $this->assertNull($actor->height);
        $this->assertNull($actor->weight);
        $this->assertNull($actor->age);
    }

    public function test_actor_stores_correct_data_types(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create([
            'height' => 180,
            'weight' => 75,
            'age' => 25,
        ]);

        // Assert
        $this->assertIsInt($actor->height);
        $this->assertIsInt($actor->weight);
        $this->assertIsInt($actor->age);
        $this->assertIsString($actor->first_name);
        $this->assertIsString($actor->last_name);
        $this->assertIsString($actor->address);
    }

    public function test_actor_can_be_deleted(): void
    {
        // Arrange
        $actor = Actor::factory()->create();
        $actorId = $actor->id;

        // Act
        $actor->delete();

        // Assert
        $this->assertDatabaseMissing('actors', ['id' => $actorId]);
    }

    public function test_multiple_actors_can_belong_to_same_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actor1 = Actor::factory()->create(['user_id' => $user->id]);
        $actor2 = Actor::factory()->create(['user_id' => $user->id]);

        // Assert
        $this->assertEquals($user->id, $actor1->user_id);
        $this->assertEquals($user->id, $actor2->user_id);
        $this->assertNotEquals($actor1->id, $actor2->id);
    }

    public function test_actor_description_can_be_long_text(): void
    {
        // Arrange
        $longDescription = str_repeat('Long description text. ', 100);

        // Act
        $actor = Actor::factory()->create(['description' => $longDescription]);

        // Assert
        $this->assertEquals($longDescription, $actor->description);
    }

    public function test_actor_can_have_special_characters_in_fields(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create([
            'first_name' => "O'Brien",
            'last_name' => 'Smith-Jones',
            'address' => 'São Paulo, Россия',
            'description' => 'Special chars: @#$%^&*()',
        ]);

        // Assert
        $this->assertEquals("O'Brien", $actor->first_name);
        $this->assertEquals('Smith-Jones', $actor->last_name);
        $this->assertEquals('São Paulo, Россия', $actor->address);
        $this->assertStringContainsString('@#$%', $actor->description);
    }

    public function test_actor_height_can_be_in_valid_range(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['height' => 165]);

        // Assert
        $this->assertEquals(165, $actor->height);
        $this->assertGreaterThan(0, $actor->height);
        $this->assertLessThan(300, $actor->height);
    }

    public function test_actor_weight_can_be_in_valid_range(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['weight' => 70]);

        // Assert
        $this->assertEquals(70, $actor->weight);
        $this->assertGreaterThan(0, $actor->weight);
        $this->assertLessThan(300, $actor->weight);
    }

    public function test_actor_age_can_be_in_valid_range(): void
    {
        // Arrange & Act
        $actor = Actor::factory()->create(['age' => 35]);

        // Assert
        $this->assertEquals(35, $actor->age);
        $this->assertGreaterThan(0, $actor->age);
        $this->assertLessThan(150, $actor->age);
    }
}
