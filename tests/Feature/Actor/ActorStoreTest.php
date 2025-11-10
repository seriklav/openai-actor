<?php

namespace Tests\Feature\Actor;

use App\Data\Actor\ActorData;
use App\Exceptions\Actor\ActorAddressMissing;
use App\Exceptions\Actor\ActorFirstNameMissing;
use App\Exceptions\Actor\ActorLastNameMissing;
use App\Models\Actor;
use App\Models\User;
use App\Services\OpenAi\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ActorStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_successfully_creates_actor_with_valid_data(): void
    {
        $email = fake()->email();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = "{$firstName} {$lastName}, 25 years old, male, 180cm, 75kg, lives in {$address}";

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

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')
                ->once()
                ->andReturn($actorData);
        });

        $response = $this->post(route('actors.store'), [
            'email' => $email,
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertDatabaseHas('actors', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
            'gender' => 'male',
            'description' => $description,
            'height' => 180,
            'weight' => 75,
            'age' => 25,
        ]);
    }

    public function test_creates_new_user_if_email_does_not_exist(): void
    {
        $email = fake()->unique()->email();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = "{$firstName} {$lastName} from {$address}";

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $this->assertDatabaseMissing('users', ['email' => $email]);

        $response = $this->post(route('actors.store'), [
            'email' => $email,
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_uses_existing_user_if_email_already_exists(): void
    {
        $user = User::factory()->create();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = "{$firstName} {$lastName} from {$address}";

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $response = $this->post(route('actors.store'), [
            'email' => $user->email,
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $this->assertEquals(1, User::query()->where('email', $user->email)->count());
        $actor = Actor::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($actor);
    }

    public function test_logs_in_user_after_creating_actor(): void
    {
        $email = fake()->email();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = "{$firstName} {$lastName} from {$address}";

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $this->assertFalse(auth()->check());

        $response = $this->post(route('actors.store'), [
            'email' => $email,
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $this->assertTrue(auth()->check());
        $this->assertEquals($email, auth()->user()->email);
    }

    public function test_returns_validation_error_when_email_is_missing(): void
    {
        $response = $this->post(route('actors.store'), [
            'description' => 'Some description',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_returns_validation_error_when_description_is_missing(): void
    {
        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_returns_validation_error_when_email_format_is_invalid(): void
    {
        $response = $this->post(route('actors.store'), [
            'email' => 'invalid-email',
            'description' => fake()->sentence(),
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_returns_validation_error_when_openai_fails_with_missing_first_name(): void
    {
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getActorData')
                ->andThrow(new ActorFirstNameMissing());
        });

        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
            'description' => fake()->sentence(),
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_returns_validation_error_when_openai_fails_with_missing_last_name(): void
    {
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getActorData')
                ->andThrow(new ActorLastNameMissing());
        });

        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
            'description' => fake()->sentence(),
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_returns_validation_error_when_openai_fails_with_missing_address(): void
    {
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getActorData')
                ->andThrow(new ActorAddressMissing());
        });

        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
            'description' => fake()->sentence(),
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_does_not_create_duplicate_actors_with_same_data(): void
    {
        $user = User::factory()->create();
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = "{$firstName} {$lastName} from {$address}";

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address' => $address,
        ]);

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $initialCount = Actor::query()->count();

        $response = $this->post(route('actors.store'), [
            'email' => $user->email,
            'description' => $description,
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals($initialCount, Actor::query()->count());
    }

    public function test_handles_long_description_text(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = str_repeat("{$firstName} {$lastName} actor description. ", 50);

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('actors', [
            'description' => $description,
        ]);
    }

    public function test_handles_special_characters_in_description(): void
    {
        $firstName = fake()->firstName();
        $lastName = "O'Brien";
        $address = "São Paulo";
        $description = "{$firstName} {$lastName}, 30 years old, lives in {$address}, speaks 日本語";

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('actors', [
            'last_name' => $lastName,
            'address' => $address,
        ]);
    }

    public function test_accepts_optional_actor_fields(): void
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $address = fake()->city();
        $description = "{$firstName} {$lastName} from {$address}";

        $actorData = new ActorData(
            firstName: $firstName,
            lastName: $lastName,
            address: $address,
            gender: null,
            description: $description,
            height: null,
            weight: null,
            age: null
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $response = $this->post(route('actors.store'), [
            'email' => fake()->email(),
            'description' => $description,
        ]);

        $response->assertRedirect(route('actors.index'));
        $actor = Actor::query()->where('first_name', $firstName)->first();
        $this->assertNotNull($actor);
        $this->assertNull($actor->height);
        $this->assertNull($actor->weight);
        $this->assertNull($actor->age);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
