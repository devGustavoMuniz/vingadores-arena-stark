<?php

use App\Notifications\Services\NotificationService;
use App\Orders\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

uses(RefreshDatabase::class);

beforeEach(function () {
    Redis::flushdb();
});

describe('NotificationService', function () {
    it('publishes an order-confirmed event to the redis stream', function () {
        $order = Order::factory()->confirmed()->create();
        $service = app(NotificationService::class);

        $service->notifyOrderConfirmed($order);

        expect(Redis::xlen('notifications:order-confirmed'))->toBe(1);
    });

    it('reads the pending notifications with the published payload', function () {
        $order = Order::factory()->confirmed()->create();
        $service = app(NotificationService::class);

        $service->notifyOrderConfirmed($order);
        $messages = $service->readPendingNotifications();

        expect($messages)->toHaveCount(1);

        $payload = array_values($messages)[0];
        expect($payload['type'])->toBe('order.confirmed')
            ->and($payload['order_id'])->toBe((string) $order->id)
            ->and($payload['user_id'])->toBe((string) $order->user_id)
            ->and($payload['event_id'])->toBe((string) $order->event_id);
    });

    it('returns an empty list when there are no notifications', function () {
        expect(app(NotificationService::class)->readPendingNotifications())->toBe([]);
    });
});
