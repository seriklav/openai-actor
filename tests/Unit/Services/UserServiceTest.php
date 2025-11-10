<?php

namespace Tests\Unit\Services;

use App\Data\User\UserAuthData;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_creates_new_user_when_email_does_not_exist(): void
    {
        // Arrange
        $email = 'newuser@example.com';
        $dto = new UserAuthData(email: $email);

        // Assert pre-condition
        $this->assertDatabaseMissing('users', ['email' => $email]);

        // Act
        $user = $this->service->getOrCreate($dto);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->email);
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check($user->password, $user->password) === false); // Password is hashed
    }

    public function test_returns_existing_user_when_email_exists(): void
    {
        // Arrange
        $email = 'existing@example.com';
        $existingUser = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('original-password'),
        ]);

        $dto = new UserAuthData(email: $email);

        // Act
        $user = $this->service->getOrCreate($dto);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($existingUser->id, $user->id);
        $this->assertEquals($existingUser->email, $user->email);
        $this->assertEquals($existingUser->password, $user->password);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_does_not_update_password_for_existing_user(): void
    {
        // Arrange
        $email = 'test@example.com';
        $originalPassword = bcrypt('original-password');
        $existingUser = User::factory()->create([
            'email' => $email,
            'password' => $originalPassword,
        ]);

        $dto = new UserAuthData(email: $email);

        // Act
        $user = $this->service->getOrCreate($dto);

        // Assert
        $this->assertEquals($originalPassword, $user->password);
        $this->assertEquals($existingUser->password, $user->password);
    }

    public function test_generates_random_password_for_new_user(): void
    {
        // Arrange
        $email1 = 'user1@example.com';
        $email2 = 'user2@example.com';

        $dto1 = new UserAuthData(email: $email1);
        $dto2 = new UserAuthData(email: $email2);

        // Act
        $user1 = $this->service->getOrCreate($dto1);
        $user2 = $this->service->getOrCreate($dto2);

        // Assert
        $this->assertNotEquals($user1->password, $user2->password);
        $this->assertNotNull($user1->password);
        $this->assertNotNull($user2->password);
    }

    public function test_handles_email_case_sensitivity_correctly(): void
    {
        // Arrange
        $email = 'Test@Example.com';
        $existingUser = User::factory()->create(['email' => $email]);

        $dto = new UserAuthData(email: $email);

        // Act
        $user = $this->service->getOrCreate($dto);

        // Assert
        $this->assertEquals($existingUser->id, $user->id);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_stores_password_as_bcrypt_hash(): void
    {
        // Arrange
        $email = 'secure@example.com';
        $dto = new UserAuthData(email: $email);

        // Act
        $user = $this->service->getOrCreate($dto);

        // Assert
        $this->assertStringStartsWith('$2y$', $user->password); // bcrypt hash identifier
        $this->assertGreaterThan(50, strlen($user->password)); // bcrypt hashes are typically 60 chars
    }

    public function test_persists_user_to_database(): void
    {
        // Arrange
        $email = 'persist@example.com';
        $dto = new UserAuthData(email: $email);

        // Act
        $user = $this->service->getOrCreate($dto);

        // Assert
        $this->assertNotNull($user->id);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $email,
        ]);
    }

    public function test_returns_same_user_on_multiple_calls_with_same_email(): void
    {
        // Arrange
        $email = 'multiple@example.com';
        $dto = new UserAuthData(email: $email);

        // Act
        $user1 = $this->service->getOrCreate($dto);
        $user2 = $this->service->getOrCreate($dto);
        $user3 = $this->service->getOrCreate($dto);

        // Assert
        $this->assertEquals($user1->id, $user2->id);
        $this->assertEquals($user2->id, $user3->id);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_handles_multiple_users_with_different_emails(): void
    {
        // Arrange
        $emails = [
            'user1@example.com',
            'user2@example.com',
            'user3@example.com',
        ];

        // Act
        $users = [];
        foreach ($emails as $email) {
            $users[] = $this->service->getOrCreate(new UserAuthData(email: $email));
        }

        // Assert
        $this->assertCount(3, $users);
        $this->assertEquals(3, User::count());
        $uniqueIds = array_unique(array_map(fn($u) => $u->id, $users));
        $this->assertCount(3, $uniqueIds);
    }
}
