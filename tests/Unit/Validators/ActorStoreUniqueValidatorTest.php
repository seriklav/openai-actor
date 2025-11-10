<?php

namespace Tests\Unit\Validators;

use App\Models\Actor;
use App\Models\User;
use App\Repositories\Actor\ActorRepository;
use App\Services\User\UserService;
use App\Validators\Actor\ActorStoreUniqueValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ActorStoreUniqueValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected ActorStoreUniqueValidator $validator;
    protected UserService $userService;
    protected ActorRepository $actorRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
        $this->actorRepository = app(ActorRepository::class);
        $this->validator = new ActorStoreUniqueValidator($this->userService, $this->actorRepository);
    }

    public function test_passes_validation_when_user_has_no_actors_with_same_description(): void
    {
        $user = User::factory()->create();
        $description = 'Unique description';

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_fails_validation_when_user_already_has_actor_with_same_description(): void
    {
        $user = User::factory()->create();
        $description = 'Duplicate description';

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertTrue($validator->errors()->has('quantity'));
    }

    public function test_creates_user_if_does_not_exist_before_validation(): void
    {
        $email = 'newuser@example.com';
        $description = 'Some description';

        $this->assertDatabaseMissing('users', ['email' => $email]);

        $request = Request::create('/test', 'POST', [
            'email' => $email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_uses_existing_user_for_validation(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $description = 'Test description';

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $userCountBefore = User::query()->count();

        $this->validator->validate($validator);

        $this->assertEquals($userCountBefore, User::query()->count());
        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_allows_same_description_for_different_users(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $description = 'Same description';

        Actor::factory()->create([
            'user_id' => $user1->id,
            'description' => $description,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user2->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_allows_different_descriptions_for_same_user(): void
    {
        $user = User::factory()->create();
        $description1 = 'First description';
        $description2 = 'Second description';

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description1,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description2,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_validation_is_case_sensitive_for_descriptions(): void
    {
        $user = User::factory()->create();
        $description = 'Case Sensitive Description';

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => strtolower($description),
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_handles_partial_description_matches(): void
    {
        $user = User::factory()->create();
        $description1 = 'Actor from New York';
        $description2 = 'Actor from New York City';

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description1,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description2,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_handles_special_characters_in_description(): void
    {
        $user = User::factory()->create();
        $description = "Actor with special chars: @#$%^&*()";

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertTrue($validator->errors()->has('quantity'));
    }

    public function test_handles_very_long_descriptions(): void
    {
        $user = User::factory()->create();
        $description = str_repeat('Very long description. ', 100);

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertTrue($validator->errors()->has('quantity'));
    }

    public function test_handles_unicode_characters_in_description(): void
    {
        $user = User::factory()->create();
        $description = 'Актёр из Москвы 日本の俳優';

        Actor::factory()->create([
            'user_id' => $user->id,
            'description' => $description,
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertTrue($validator->errors()->has('quantity'));
    }

    public function test_handles_empty_description(): void
    {
        $user = User::factory()->create();
        $description = '';

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }

    public function test_validates_correctly_when_user_has_multiple_actors(): void
    {
        $user = User::factory()->create();
        $description1 = 'First actor description';
        $description2 = 'Second actor description';
        $description3 = 'Third actor description';

        Actor::factory()->create(['user_id' => $user->id, 'description' => $description1]);
        Actor::factory()->create(['user_id' => $user->id, 'description' => $description2]);

        $request = Request::create('/test', 'POST', [
            'email' => $user->email,
            'description' => $description3,
        ]);
        $this->app->instance('request', $request);

        $validator = Validator::make($request->all(), []);

        $this->validator->validate($validator);

        $this->assertFalse($validator->errors()->has('quantity'));
    }
}
