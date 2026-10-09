<?php

namespace Database\Factories\Inventory;

use App\Inventory\Models\Event;
use App\Inventory\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'code' => strtoupper($this->faker->bothify('????####')),
            'status' => 'sold',
            'reserved_until' => null,
        ];
    }

    public function reserved(): static
    {
        return $this->state(fn () => [
            'status' => 'reserved',
            'reserved_until' => now()->addMinutes(10),
        ]);
    }
}
