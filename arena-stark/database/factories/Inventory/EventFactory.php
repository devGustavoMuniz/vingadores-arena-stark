<?php

namespace Database\Factories\Inventory;

use App\Inventory\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $total = $this->faker->numberBetween(50, 500);

        return [
            'name' => $this->faker->words(3, true).' Concert',
            'description' => $this->faker->paragraph(),
            'venue' => $this->faker->city().', '.$this->faker->streetName(),
            'starts_at' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'total_tickets' => $total,
            'available_tickets' => $this->faker->numberBetween(0, $total),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'status' => 'active',
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn () => ['available_tickets' => 0]);
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active', 'available_tickets' => 100]);
    }
}
