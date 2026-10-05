<?php

use App\Inventory\Models\Event;
use App\Inventory\Services\InventoryService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    Redis::flushdb();
});

describe('InventoryService', function () {
    it('returns available stock from cache (cache-aside)', function () {
        $event = Event::factory()->create(['available_tickets' => 50]);

        $service = app(InventoryService::class);
        $stock = $service->getAvailableStock($event->id);

        expect($stock)->toBe(50);
    });

    it('warms redis cache from database', function () {
        $event = Event::factory()->create(['available_tickets' => 100]);

        $service = app(InventoryService::class);
        $service->warmStockCache($event->id);

        $key = 'inventory:stock:'.$event->id;
        expect((int) Redis::get($key))->toBe(100);
    });

    it('reserves a ticket atomically and returns a token', function () {
        $event = Event::factory()->create(['available_tickets' => 10]);
        $user = User::factory()->create();

        $service = app(InventoryService::class);
        $service->warmStockCache($event->id);

        $token = $service->reserveTicket($event->id, $user->id);

        expect($token)->toBeString()->not->toBeEmpty();

        $key = 'inventory:stock:'.$event->id;
        expect((int) Redis::get($key))->toBe(9);
    });

    it('returns null when stock is zero', function () {
        $event = Event::factory()->create(['available_tickets' => 0]);
        $user = User::factory()->create();

        $service = app(InventoryService::class);
        $service->warmStockCache($event->id);

        $token = $service->reserveTicket($event->id, $user->id);

        expect($token)->toBeNull();
    });

    it('confirms a reservation and removes it from redis', function () {
        $event = Event::factory()->create(['available_tickets' => 5]);
        $user = User::factory()->create();

        $service = app(InventoryService::class);
        $service->warmStockCache($event->id);

        $token = $service->reserveTicket($event->id, $user->id);
        $reservation = $service->confirmReservation($token);

        expect($reservation)->toBeArray()
            ->and($reservation['event_id'])->toBe($event->id)
            ->and($reservation['user_id'])->toBe($user->id);

        // Token should be consumed (deleted)
        expect($service->confirmReservation($token))->toBeNull();
    });

    it('releases stock back to redis on failure', function () {
        $event = Event::factory()->create(['available_tickets' => 3]);

        $service = app(InventoryService::class);
        $service->warmStockCache($event->id);

        $service->releaseStock($event->id);

        $key = 'inventory:stock:'.$event->id;
        expect((int) Redis::get($key))->toBe(4);
    });
});
