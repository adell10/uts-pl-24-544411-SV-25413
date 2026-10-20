<?php

namespace Database\Factories;

use App\Models\Cafe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cafe>
 */
class CafeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Kopi '.ucfirst(fake()->word()),
            'area' => fake()->randomElement(['Seturan', 'Kaliurang', 'Gejayan', 'Kotabaru', 'Prawirotaman']),
            'status' => 'wishlist',
            'price_level' => fake()->numberBetween(1, 3),
            'rating' => null,
            'work_friendly' => fake()->boolean(),
            'facilities' => fake()->randomElements(Cafe::FACILITIES, 2),
            'favorite_menu' => null,
            'notes' => null,
            'last_visited_at' => null,
            'visit_count' => 0,
            'color' => fake()->randomElement(Cafe::COLORS),
        ];
    }

    public function visited(): static
    {
        return $this->state(fn () => [
            'status' => 'visited',
            'rating' => fake()->numberBetween(3, 5),
            'last_visited_at' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
            'visit_count' => fake()->numberBetween(1, 6),
        ]);
    }
}