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

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_successfully_creates_actor_with_valid_data(): void
    {
        // Arrange
        $email = 'actor@example.com';
        $description = 'John Doe, 25 years old, male, 180cm, 75kg, lives in New York';

        $actorData = new ActorData(
            firstName: 'John',
            lastName: 'Doe',
            address: 'New York',
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

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => $email,
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertDatabaseHas('actors', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'New York',
            'gender' => 'male',
            'description' => $description,
            'height' => 180,
            'weight' => 75,
            'age' => 25,
        ]);
    }

    public function test_creates_new_user_if_email_does_not_exist(): void
    {
        // Arrange
        $email = 'newuser@example.com';
        $description = 'Valid actor description';

        $actorData = new ActorData(
            firstName: 'Jane',
            lastName: 'Smith',
            address: 'London',
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        // Assert pre-condition
        $this->assertDatabaseMissing('users', ['email' => $email]);

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => $email,
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_uses_existing_user_if_email_already_exists(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $description = 'Valid actor description';

        $actorData = new ActorData(
            firstName: 'Bob',
            lastName: 'Johnson',
            address: 'Paris',
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => $user->email,
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $this->assertEquals(1, User::where('email', $user->email)->count());
        $actor = Actor::where('user_id', $user->id)->first();
        $this->assertNotNull($actor);
    }

    public function test_logs_in_user_after_creating_actor(): void
    {
        // Arrange
        $email = 'login@example.com';
        $description = 'Valid description';

        $actorData = new ActorData(
            firstName: 'Test',
            lastName: 'User',
            address: 'City',
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        // Assert pre-condition
        $this->assertFalse(auth()->check());

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => $email,
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $this->assertTrue(auth()->check());
        $this->assertEquals($email, auth()->user()->email);
    }

    public function test_returns_validation_error_when_email_is_missing(): void
    {
        // Act
        $response = $this->post(route('actors.store'), [
            'description' => 'Some description',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    public function test_returns_validation_error_when_description_is_missing(): void
    {
        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
        ]);

        // Assert
        $response->assertSessionHasErrors(['description']);
    }

    public function test_returns_validation_error_when_email_format_is_invalid(): void
    {
        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'invalid-email',
            'description' => 'Some description',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    public function test_returns_validation_error_when_openai_fails_with_missing_first_name(): void
    {
        // Arrange
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getActorData')
                ->andThrow(new ActorFirstNameMissing());
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'Invalid description',
        ]);

        // Assert
        $response->assertSessionHasErrors(['description']);
    }

    public function test_returns_validation_error_when_openai_fails_with_missing_last_name(): void
    {
        // Arrange
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getActorData')
                ->andThrow(new ActorLastNameMissing());
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'Invalid description',
        ]);

        // Assert
        $response->assertSessionHasErrors(['description']);
    }

    public function test_returns_validation_error_when_openai_fails_with_missing_address(): void
    {
        // Arrange
        $this->mock(OpenAiService::class, function ($mock) {
            $mock->shouldReceive('getActorData')
                ->andThrow(new ActorAddressMissing());
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'Invalid description',
        ]);

        // Assert
        $response->assertSessionHasErrors(['description']);
    }

    public function test_does_not_create_duplicate_actors_with_same_data(): void
    {
        // Arrange
        $user = User::factory()->create();
        $description = 'Unique actor description';

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'New York',
        ]);

        $actorData = new ActorData(
            firstName: 'John',
            lastName: 'Doe',
            address: 'New York',
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        $initialCount = Actor::count();

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => $user->email,
            'description' => $description,
        ]);

        // Assert
        $response->assertSessionHasErrors();
        $this->assertEquals($initialCount, Actor::count());
    }

    public function test_handles_long_description_text(): void
    {
        // Arrange
        $description = str_repeat('This is a very long actor description. ', 50);

        $actorData = new ActorData(
            firstName: 'Long',
            lastName: 'Description',
            address: 'City',
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'long@example.com',
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('actors', [
            'description' => $description,
        ]);
    }

    public function test_handles_special_characters_in_description(): void
    {
        // Arrange
        $description = "John O'Brien, 30 years old, lives in São Paulo, speaks 日本語";

        $actorData = new ActorData(
            firstName: "John",
            lastName: "O'Brien",
            address: "São Paulo",
            description: $description
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'special@example.com',
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $this->assertDatabaseHas('actors', [
            'last_name' => "O'Brien",
        ]);
    }

    public function test_accepts_optional_actor_fields(): void
    {
        // Arrange
        $description = 'Minimal actor data';

        $actorData = new ActorData(
            firstName: 'Minimal',
            lastName: 'Data',
            address: 'Unknown',
            description: $description,
            height: null,
            weight: null,
            age: null,
            gender: null
        );

        $this->mock(OpenAiService::class, function ($mock) use ($actorData) {
            $mock->shouldReceive('getActorData')->andReturn($actorData);
        });

        // Act
        $response = $this->post(route('actors.store'), [
            'email' => 'minimal@example.com',
            'description' => $description,
        ]);

        // Assert
        $response->assertRedirect(route('actors.index'));
        $actor = Actor::where('first_name', 'Minimal')->first();
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
