<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kingdom_hall_id' => \App\Models\KingdomHall::factory(),
            'name' => fake()->name(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'role' => fake()->randomElement(['coordinator', 'maintenance', 'admin', 'elder']),
            'notify_email' => true,
            'notify_sms' => fake()->boolean(),
            'active' => true,
        ];
    }
}
