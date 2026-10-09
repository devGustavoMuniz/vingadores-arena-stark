<?php

namespace App\Orders\Services;

use App\Inventory\Services\InventoryService;
use App\Notifications\Services\NotificationService;
use App\Orders\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Creates a reservation (Redis TTL) for the user.
     * Returns a reservation token to be used in the confirm step.
     *
     * @throws \RuntimeException when stock is unavailable
     */
    public function reserve(int $eventId, int $userId): string
    {
        $token = $this->inventoryService->reserveTicket($eventId, $userId);

        if ($token === null) {
            throw new \RuntimeException('No tickets available for this event.');
        }

        return $token;
    }

    /**
     * Confirms the purchase using the reservation token.
     * Atomically: validates reservation → persists ticket and order → notifies user.
     *
     * @throws \RuntimeException when reservation is expired or invalid
     */
    public function confirm(string $reservationToken, int $userId): Order
    {
        $reservation = $this->inventoryService->confirmReservation($reservationToken);

        if ($reservation === null) {
            throw new \RuntimeException('Reservation expired or not found.');
        }

        if ((int) $reservation['user_id'] !== $userId) {
            throw new \RuntimeException('Reservation does not belong to this user.');
        }

        $eventId = (int) $reservation['event_id'];

        return DB::transaction(function () use ($eventId, $userId, $reservationToken) {
            $ticket = $this->inventoryService->registerSale($eventId);

            $order = Order::create([
                'user_id' => $userId,
                'event_id' => $eventId,
                'ticket_id' => $ticket->id,
                'status' => 'confirmed',
                'amount' => $ticket->event->price,
                'reservation_token' => $reservationToken,
            ]);

            // Notify user asynchronously via Redis Streams
            try {
                $this->notificationService->notifyOrderConfirmed($order);
            } catch (\Throwable $e) {
                Log::warning('Failed to dispatch order confirmation notification', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $order;
        });
    }
}
