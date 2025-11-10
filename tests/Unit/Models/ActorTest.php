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
        $user = User::factory()->create();
        $actor = Actor::factory()->create(['user_id' => $user->id]);

        $actorUser = $actor->user;

        $this->assertInstanceOf(User::class, $actorUser);
        $this->assertEquals($user->id, $actorUser->id);
    }

    public function test_actor_has_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'description' => fake()->sentence(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->city(),
            'height' => 180,
            'weight' => 75,
            'gender' => GenderEnum::MALE->value,
            'age' => 25,
        ];

        $actor = Actor::query()->create($data);

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
        $actor = Actor::factory()->create(['gender' => GenderEnum::MALE->value]);

        $this->assertInstanceOf(GenderEnum::class, $actor->gender);
        $this->assertEquals('male', $actor->gender->value);
    }

    public function test_actor_can_have_male_gender(): void
    {
        $actor = Actor::factory()->create(['gender' => GenderEnum::MALE->value]);

        $this->assertEquals(GenderEnum::MALE, $actor->gender);
    }

    public function test_actor_can_have_female_gender(): void
    {
        $actor = Actor::factory()->create(['gender' => GenderEnum::FEMALE->value]);

        $this->assertEquals(GenderEnum::FEMALE, $actor->gender);
    }

    public function test_actor_can_have_other_gender(): void
    {
        $actor = Actor::factory()->create(['gender' => GenderEnum::OTHER->value]);

        $this->assertEquals(GenderEnum::OTHER, $actor->gender);
    }

    public function test_actor_has_timestamps(): void
    {
        $actor = Actor::factory()->create();

        $this->assertNotNull($actor->created_at);
        $this->assertNotNull($actor->updated_at);
    }

    public function test_actor_updates_timestamp_on_modification(): void
    {
        $actor = Actor::factory()->create();
        $oldUpdatedAt = $actor->updated_at;

        sleep(1);

        $actor->update(['first_name' => 'Updated Name']);

        $this->assertTrue($actor->updated_at->isAfter($oldUpdatedAt));
    }

    public function test_actor_can_be_created_with_minimal_data(): void
    {
        $user = User::factory()->create();

        $firstName = fake()->firstName();
        $actor = Actor::query()->create([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => fake()->lastName(),
            'address' => fake()->city(),
            'gender' => GenderEnum::MALE->value,
            'description' => fake()->sentence(),
        ]);

        $this->assertNotNull($actor->id);
        $this->assertEquals($firstName, $actor->first_name);
    }

    public function test_actor_can_have_null_optional_fields(): void
    {
        $user = User::factory()->create();

        $actor = Actor::query()->create([
            'user_id' => $user->id,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->city(),
            'gender' => GenderEnum::FEMALE->value,
            'description' => fake()->sentence(),
            'height' => null,
            'weight' => null,
            'age' => null,
        ]);

        $this->assertNotNull($actor->description);
        $this->assertNull($actor->height);
        $this->assertNull($actor->weight);
        $this->assertNull($actor->age);
    }

    public function test_actor_stores_correct_data_types(): void
    {
        $actor = Actor::factory()->create([
            'height' => 180,
            'weight' => 75,
            'age' => 25,
        ]);

        $this->assertIsInt($actor->height);
        $this->assertIsInt($actor->weight);
        $this->assertIsInt($actor->age);
        $this->assertIsString($actor->first_name);
        $this->assertIsString($actor->last_name);
        $this->assertIsString($actor->address);
    }

    public function test_actor_can_be_deleted(): void
    {
        $actor = Actor::factory()->create();
        $actorId = $actor->id;

        $actor->delete();

        $this->assertDatabaseMissing('actors', ['id' => $actorId]);
    }

    public function test_multiple_actors_can_belong_to_same_user(): void
    {
        $user = User::factory()->create();

        $actor1 = Actor::factory()->create(['user_id' => $user->id]);
        $actor2 = Actor::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $actor1->user_id);
        $this->assertEquals($user->id, $actor2->user_id);
        $this->assertNotEquals($actor1->id, $actor2->id);
    }

    public function test_actor_description_can_be_long_text(): void
    {
        $longDescription = str_repeat(fake()->sentence() . ' ', 100);

        $actor = Actor::factory()->create(['description' => $longDescription]);

        $this->assertEquals($longDescription, $actor->description);
    }

    public function test_actor_can_have_special_characters_in_fields(): void
    {
        $firstName = "O'Brien";
        $lastName = 'Smith-Jones';
        $address = 'São Paulo';

        $actor = Actor::factory()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
            'description' => fake()->sentence(),
        ]);

        $this->assertEquals($firstName, $actor->first_name);
        $this->assertEquals($lastName, $actor->last_name);
        $this->assertEquals($address, $actor->address);
    }

    public function test_actor_height_can_be_in_valid_range(): void
    {
        $actor = Actor::factory()->create(['height' => 165]);

        $this->assertEquals(165, $actor->height);
        $this->assertGreaterThan(0, $actor->height);
        $this->assertLessThan(300, $actor->height);
    }

    public function test_actor_weight_can_be_in_valid_range(): void
    {
        $actor = Actor::factory()->create(['weight' => 70]);

        $this->assertEquals(70, $actor->weight);
        $this->assertGreaterThan(0, $actor->weight);
        $this->assertLessThan(300, $actor->weight);
    }

    public function test_actor_age_can_be_in_valid_range(): void
    {
        $actor = Actor::factory()->create(['age' => 35]);

        $this->assertEquals(35, $actor->age);
        $this->assertGreaterThan(0, $actor->age);
        $this->assertLessThan(150, $actor->age);
    }
}
