<?php

use App\Inventory\Models\Event;
use App\Inventory\Services\InventoryService;
use App\Models\User;
use App\Notifications\Services\NotificationService;
use App\Orders\Models\Order;
use App\Orders\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    Redis::flushdb();
});

describe('OrderService', function () {
    it('reserves a ticket and returns a token', function () {
        $event = Event::factory()->create(['available_tickets' => 5]);
        $user = User::factory()->create();
        app(InventoryService::class)->warmStockCache($event->id);

        $token = app(OrderService::class)->reserve($event->id, $user->id);

        expect($token)->toBeString()->not->toBeEmpty();
    });

    it('throws when there is no stock to reserve', function () {
        $event = Event::factory()->create(['available_tickets' => 0]);
        $user = User::factory()->create();
        app(InventoryService::class)->warmStockCache($event->id);

        app(OrderService::class)->reserve($event->id, $user->id);
    })->throws(RuntimeException::class, 'No tickets available');

    it('confirms a reservation, persists the order and decrements database stock', function () {
        $event = Event::factory()->create(['available_tickets' => 5, 'price' => 100]);
        $user = User::factory()->create();
        app(InventoryService::class)->warmStockCache($event->id);

        $service = app(OrderService::class);
        $order = $service->confirm($service->reserve($event->id, $user->id), $user->id);

        expect($order)->toBeInstanceOf(Order::class)
            ->and($order->status)->toBe('confirmed')
            ->and($order->user_id)->toBe($user->id)
            ->and($order->event_id)->toBe($event->id)
            ->and((float) $order->amount)->toBe(100.0)
            ->and($order->ticket_id)->not->toBeNull()
            ->and($event->fresh()->available_tickets)->toBe(4);
    });

    it('rejects an unknown or expired reservation token', function () {
        $user = User::factory()->create();

        app(OrderService::class)->confirm('token-inexistente', $user->id);
    })->throws(RuntimeException::class, 'Reservation expired or not found.');

    it('rejects a reservation that belongs to another user', function () {
        $event = Event::factory()->create(['available_tickets' => 5]);
        $owner = User::factory()->create();
        $other = User::factory()->create();
        app(InventoryService::class)->warmStockCache($event->id);

        $service = app(OrderService::class);
        $service->confirm($service->reserve($event->id, $owner->id), $other->id);
    })->throws(RuntimeException::class, 'does not belong to this user');

    it('does not fail the order when the notification cannot be published', function () {
        $event = Event::factory()->create(['available_tickets' => 5]);
        $user = User::factory()->create();
        app(InventoryService::class)->warmStockCache($event->id);

        $notifications = Mockery::mock(NotificationService::class);
        $notifications->shouldReceive('notifyOrderConfirmed')->once()->andThrow(new RuntimeException('redis down'));

        $service = new OrderService(app(InventoryService::class), $notifications);
        $order = $service->confirm($service->reserve($event->id, $user->id), $user->id);

        expect($order->status)->toBe('confirmed');
    });
});
