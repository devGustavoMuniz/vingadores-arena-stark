<?php

namespace App\Inventory\Services;

use App\Inventory\Models\Event;
use App\Inventory\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class InventoryService
{
    private const STOCK_KEY_PREFIX = 'inventory:stock:';

    private const RESERVATION_KEY_PREFIX = 'inventory:reservation:';

    private const RESERVATION_TTL_SECONDS = 600; // 10 minutes

    /**
     * Returns the current available stock for an event.
     * Uses cache-aside pattern: checks Redis first, falls back to DB.
     */
    public function getAvailableStock(int $eventId): int
    {
        $key = self::STOCK_KEY_PREFIX.$eventId;

        return (int) Cache::remember($key, 300, function () use ($eventId) {
            $event = Event::findOrFail($eventId);

            return $event->available_tickets;
        });
    }

    /**
     * Atomically decrements stock in Redis and creates a reservation with TTL.
     * Returns a reservation token string, or null if stock is unavailable.
     */
    public function reserveTicket(int $eventId, int $userId): ?string
    {
        $stockKey = self::STOCK_KEY_PREFIX.$eventId;

        // Lua script for atomic check-and-decrement
        $luaScript = <<<'LUA'
            local key = KEYS[1]
            local stock = redis.call('GET', key)
            if stock == false then
                return -1
            end
            if tonumber(stock) <= 0 then
                return 0
            end
            return redis.call('DECR', key)
        LUA;

        $result = Redis::eval($luaScript, 1, $stockKey);

        if ($result <= 0) {
            return null;
        }

        $token = bin2hex(random_bytes(16));
        $reservationKey = self::RESERVATION_KEY_PREFIX.$token;

        Redis::setex($reservationKey, self::RESERVATION_TTL_SECONDS, json_encode([
            'event_id' => $eventId,
            'user_id' => $userId,
            'reserved_at' => now()->toIso8601String(),
        ]));

        return $token;
    }

    /**
     * Confirms a reservation by its token.
     * Returns the reservation data or null if expired/invalid.
     */
    public function confirmReservation(string $token): ?array
    {
        $reservationKey = self::RESERVATION_KEY_PREFIX.$token;
        $data = Redis::get($reservationKey);

        if ($data === null) {
            return null;
        }

        Redis::del($reservationKey);

        return json_decode($data, true);
    }

    /**
     * Persists the definitive sale of one ticket: creates the sold ticket and
     * decrements the stock stored in the database. Must run inside the caller's
     * transaction. Returns the ticket with its event loaded.
     */
    public function registerSale(int $eventId): Ticket
    {
        $ticket = Ticket::create([
            'event_id' => $eventId,
            'code' => strtoupper(bin2hex(random_bytes(4))),
            'status' => 'sold',
            'reserved_until' => null,
        ]);

        Event::where('id', $eventId)->decrement('available_tickets');

        return $ticket->load('event');
    }

    /**
     * Releases stock back to Redis (e.g. on order failure or TTL expiry).
     */
    public function releaseStock(int $eventId): void
    {
        $key = self::STOCK_KEY_PREFIX.$eventId;
        Redis::incr($key);
    }

    /**
     * Seeds the Redis stock key from the database (called on cache warm-up).
     */
    public function warmStockCache(int $eventId): void
    {
        $event = Event::findOrFail($eventId);
        $key = self::STOCK_KEY_PREFIX.$eventId;
        Redis::set($key, $event->available_tickets);
    }
}
