<?php

namespace Database\Factories;

use App\Enums\Actor\GenderEnum;
use App\Models\Actor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Actor>
 */
class ActorFactory extends Factory
{
    protected $model = Actor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->city() . ', ' . fake()->country(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'description' => fake()->paragraph(),
            'height' => fake()->numberBetween(150, 200),
            'weight' => fake()->numberBetween(50, 120),
            'age' => fake()->numberBetween(18, 80),
        ];
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'male',
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'female',
        ]);
    }

    public function other(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'other',
        ]);
    }

    public function withHeight(int $height): static
    {
        return $this->state(fn (array $attributes) => [
            'height' => $height,
        ]);
    }

    public function withWeight(int $weight): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $weight,
        ]);
    }

    public function withAge(int $age): static
    {
        return $this->state(fn (array $attributes) => [
            'age' => $age,
        ]);
    }
}
