<?php

namespace Tests\Feature\Actor;

use App\Models\Actor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_to_login_when_user_is_not_authenticated(): void
    {
        // Act
        $response = $this->get(route('actors.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_displays_actors_index_page_when_authenticated(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('actors.index'));

        // Assert
        $response->assertOk();
        $response->assertViewIs('actors.index');
    }

    public function test_shows_only_authenticated_users_actors(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1Actors = Actor::factory()->count(3)->create(['user_id' => $user1->id]);
        $user2Actors = Actor::factory()->count(2)->create(['user_id' => $user2->id]);

        // Act
        $response = $this->actingAs($user1)->get(route('actors.index'));

        // Assert
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
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->count(25)->create(['user_id' => $user->id]);

        // Act - Page 1
        $responsePage1 = $this->actingAs($user)->get(route('actors.index', ['per_page' => 10]));
        $actorsPage1 = $responsePage1->viewData('actors');

        // Act - Page 2
        $responsePage2 = $this->actingAs($user)->get(route('actors.index', ['per_page' => 10, 'page' => 2]));
        $actorsPage2 = $responsePage2->viewData('actors');

        // Assert
        $this->assertCount(10, $actorsPage1->items());
        $this->assertCount(10, $actorsPage2->items());
        $this->assertEquals(25, $actorsPage1->total());
        $this->assertEquals(1, $actorsPage1->currentPage());
        $this->assertEquals(2, $actorsPage2->currentPage());
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

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['first_name' => 'Alex']));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(2, $actors->items());
    }

    public function test_filters_actors_by_last_name(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => 'Smith']);
        Actor::factory()->create(['user_id' => $user->id, 'last_name' => 'Johnson']);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['last_name' => 'Smith']));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals('Smith', $actors->items()[0]->last_name);
    }

    public function test_filters_actors_by_address(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'address' => 'New York']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => 'Los Angeles']);
        Actor::factory()->create(['user_id' => $user->id, 'address' => 'New Jersey']);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['address' => 'New']));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(2, $actors->items());
    }

    public function test_filters_actors_by_gender(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'male']);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'male']);
        Actor::factory()->create(['user_id' => $user->id, 'gender' => 'female']);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['gender' => 'male']));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(2, $actors->items());
    }

    public function test_filters_actors_by_height(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'height' => 180]);
        Actor::factory()->create(['user_id' => $user->id, 'height' => 175]);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['height' => 180]));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals(180, $actors->items()[0]->height);
    }

    public function test_filters_actors_by_weight(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 75]);
        Actor::factory()->create(['user_id' => $user->id, 'weight' => 80]);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['weight' => 75]));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals(75, $actors->items()[0]->weight);
    }

    public function test_filters_actors_by_age(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'age' => 25]);
        Actor::factory()->create(['user_id' => $user->id, 'age' => 30]);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['age' => 25]));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
        $this->assertEquals(25, $actors->items()[0]->age);
    }

    public function test_filters_actors_by_description(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Experienced actor']);
        Actor::factory()->create(['user_id' => $user->id, 'description' => 'Newcomer']);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', ['description' => 'Experienced']));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
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

        // Act
        $response = $this->actingAs($user)->get(route('actors.index', [
            'first_name' => 'John',
            'gender' => 'male',
            'age' => 25,
        ]));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(1, $actors->items());
    }

    public function test_shows_empty_list_when_no_actors_exist(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('actors.index'));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(0, $actors->items());
    }

    public function test_orders_actors_by_latest_first(): void
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

        // Act
        $response = $this->actingAs($user)->get(route('actors.index'));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertEquals($newActor->id, $actors->items()[0]->id);
        $this->assertEquals($oldActor->id, $actors->items()[1]->id);
    }

    public function test_ignores_actors_from_other_users_in_filters(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Actor::factory()->create([
            'user_id' => $user2->id,
            'first_name' => 'John',
        ]);

        // Act
        $response = $this->actingAs($user1)->get(route('actors.index', ['first_name' => 'John']));

        // Assert
        $response->assertOk();
        $actors = $response->viewData('actors');
        $this->assertCount(0, $actors->items());
    }

    public function test_returns_actors_view_with_correct_data_structure(): void
    {
        // Arrange
        $user = User::factory()->create();
        Actor::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user)->get(route('actors.index'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('actors');
        $actors = $response->viewData('actors');
        $this->assertNotNull($actors);
    }
}
