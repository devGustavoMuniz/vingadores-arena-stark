<?php

namespace App\Notifications\Services;

use App\Orders\Models\Order;
use Illuminate\Support\Facades\Redis;

class NotificationService
{
    private const STREAM_KEY = 'notifications:order-confirmed';

    /**
     * Publishes an order-confirmed event to Redis Streams.
     * The stream consumer (future microservice) will process and deliver the notification.
     */
    public function notifyOrderConfirmed(Order $order): void
    {
        Redis::xadd(self::STREAM_KEY, '*', [
            'type' => 'order.confirmed',
            'order_id' => (string) $order->id,
            'user_id' => (string) $order->user_id,
            'event_id' => (string) $order->event_id,
            'ticket_code' => $order->ticket->code ?? '',
            'amount' => (string) $order->amount,
            'occurred_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Reads pending notifications from the stream (for a consumer group).
     * Returns an array of messages with their stream IDs.
     */
    public function readPendingNotifications(int $count = 10): array
    {
        $messages = Redis::xread([self::STREAM_KEY => '0-0'], $count);

        if (empty($messages)) {
            return [];
        }

        return $messages[self::STREAM_KEY] ?? [];
    }
}
