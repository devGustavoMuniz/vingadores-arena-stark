<?php

namespace Database\Factories\Orders;

use App\Inventory\Models\Event;
use App\Inventory\Models\Ticket;
use App\Models\User;
use App\Orders\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'ticket_id' => null,
            'status' => 'pending',
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'reservation_token' => bin2hex(random_bytes(16)),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'ticket_id' => Ticket::factory(),
        ]);
    }
}
