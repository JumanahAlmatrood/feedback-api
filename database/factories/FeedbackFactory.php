<?php

namespace Database\Factories;

use App\Enums\FeedbackCategory;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'rating' => fake()->numberBetween(1, 5),
            'category' => fake()->randomElement(FeedbackCategory::cases()),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
