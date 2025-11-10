<?php

namespace Tests\Feature\Actor;

use App\Enums\Actor\GenderEnum;
use App\Models\Actor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_displays_actors_index_page_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('actors.index'));

        $response->assertOk();
        $response->assertViewIs('actors.index');
    }

    public function test_shows_only_authenticated_users_actors(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Actor::factory()->count(3)->create(['user_id' => $user1->id]);
        Actor::factory()->count(2)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('actors.index'));

        $response->assertOk();
        $response->assertViewHas('actors');

        $actors = $response->viewData('actors');
        $this->assertCount(3, $actors->items());

        foreach ($actors->items() as $actor) {
            $this->assertEquals($user1->id, $actor->user_id);
        }
    }

    public function test_paginates_actors_correctly(): void
    {
        $user = User::factory()->create();
        Actor::factory()->count(25)->create(['user_id' => $user->id]);

        $responsePage1 = $this->actingAs($user)->get(route('actors.index', ['per_page' => 10]));
        $actorsPage1 = $responsePage1->viewData('actors');

        $responsePage2 = $this->actingAs($user)->get(route('actors.index', ['per_page' => 10, 'page' => 2]));
        $actorsPage2 = $responsePage2->viewData('actors');

        $this->assertCount(10, $actorsPage1->items());
        $this->assertCount(10, $actorsPage2->items());
        $this->assertEquals(25, $actorsPage1->total());
        $this->assertEquals(1, $actorsPage1->currentPage());
        $this->assertEquals(2, $actorsPage2->currentPage());
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

        $response = $this->actingAs($user)->get(route('actors.index', ['first_name' => $searchName]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(2, $actors->items());
    }

    public function test_filters_actors_by_last_name(): void
    {
        $user = User::factory()->create();
        $lastName = fake()->lastName();

        Actor::factory()->create(['user_id' => $user->id, 'last_name' => $lastName]);
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => fake()->lastName()]);

        $response = $this->actingAs($user)->get(route('actors.index', ['last_name' => $lastName]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals($lastName, $actors->items()[0]->last_name);
    }

    public function test_filters_actors_by_address(): void
    {
        $user = User::factory()->create();
        $searchAddress = fake()->country();

        Actor::factory()->create(['user_id' => $user->id, 'address' => $searchAddress . ' York']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => fake()->city()]);
        Actor::factory()->create(['user_id' => $user->id, 'address' => $searchAddress . ' Jersey']);

        $response = $this->actingAs($user)->get(route('actors.index', ['address' => $searchAddress]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(2, $actors->items());
    }

    public function test_filters_actors_by_gender(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'gender' => GenderEnum::MALE->value]);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => GenderEnum::MALE->value]);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => GenderEnum::FEMALE->value]);

        $response = $this->actingAs($user)->get(route('actors.index', ['gender' => GenderEnum::MALE->value]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(2, $actors->items());
    }

    public function test_filters_actors_by_height(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'height' => 180]);
        Actor::factory()->create(['user_id' => $user->id, 'height' => 175]);

        $response = $this->actingAs($user)->get(route('actors.index', ['height' => 180]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals(180, $actors->items()[0]->height);
    }

    public function test_filters_actors_by_weight(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 75]);
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 80]);

        $response = $this->actingAs($user)->get(route('actors.index', ['weight' => 75]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals(75, $actors->items()[0]->weight);
    }

    public function test_filters_actors_by_age(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'age' => 25]);
        Actor::factory()->create(['user_id' => $user->id, 'age' => 30]);

        $response = $this->actingAs($user)->get(route('actors.index', ['age' => 25]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals(25, $actors->items()[0]->age);
    }

    public function test_filters_actors_by_description(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Experienced actor']);
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Newcomer']);

        $response = $this->actingAs($user)->get(route('actors.index', ['description' => 'Experienced']));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
    }

    public function test_combines_multiple_filters(): void
    {
        $user = User::factory()->create();
        $firstName = fake()->firstName();

        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'gender' => GenderEnum::MALE->value,
            'age' => 25,
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'gender' => GenderEnum::MALE->value,
            'age' => 30,
        ]);
        Actor::factory()->create([
            'user_id' => $user->id,
            'first_name' => fake()->firstName(),
            'gender' => GenderEnum::FEMALE->value,
            'age' => 25,
        ]);

        $response = $this->actingAs($user)->get(route('actors.index', [
            'first_name' => $firstName,
            'gender' => GenderEnum::MALE->value,
            'age' => 25,
        ]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
    }

    public function test_shows_empty_list_when_no_actors_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('actors.index'));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(0, $actors->items());
    }

    public function test_orders_actors_by_latest_first(): void
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

        $response = $this->actingAs($user)->get(route('actors.index'));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertEquals($newActor->id, $actors->items()[0]->id);
        $this->assertEquals($oldActor->id, $actors->items()[1]->id);
    }

    public function test_ignores_actors_from_other_users_in_filters(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $firstName = fake()->firstName();

        Actor::factory()->create([
            'user_id' => $user2->id,
            'first_name' => $firstName,
        ]);

        $response = $this->actingAs($user1)->get(route('actors.index', ['first_name' => $firstName]));

        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(0, $actors->items());
    }

    public function test_returns_actors_view_with_correct_data_structure(): void
    {
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('actors.index'));

        $response->assertOk();
        $response->assertViewHas('actors');
        $actors = $response->viewData('actors');
        $this->assertNotNull($actors);
    }
}
