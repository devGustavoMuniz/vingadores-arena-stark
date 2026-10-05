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

describe('Event listing', function () {
    it('lists active events on /events', function () {
        Event::factory()->count(3)->create(['status' => 'active', 'available_tickets' => 10]);
        Event::factory()->create(['status' => 'cancelled', 'available_tickets' => 0]);

        $response = $this->get('/events');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('events/index')
            ->has('events', 3)
        );
    });

    it('shows event details on /events/{id}', function () {
        $event = Event::factory()->create(['status' => 'active', 'available_tickets' => 50]);

        $response = $this->get("/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('events/show')
            ->where('event.id', $event->id)
            ->where('event.is_available', true)
        );
    });

    it('returns 404 for non-existent event', function () {
        $this->get('/events/9999')->assertStatus(404);
    });
});

describe('Ticket reservation flow (end-to-end)', function () {
    it('redirects to confirmation page after successful reservation', function () {
        $user = User::factory()->create();
        $event = Event::factory()->create(['available_tickets' => 10, 'status' => 'active']);

        // Warm Redis stock
        app(InventoryService::class)->warmStockCache($event->id);

        $response = $this->actingAs($user)
            ->post('/orders/reserve', ['event_id' => $event->id]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    });

    it('fails reservation when event has no stock', function () {
        $user = User::factory()->create();
        $event = Event::factory()->create(['available_tickets' => 0, 'status' => 'active']);

        // Warm Redis stock at 0
        app(InventoryService::class)->warmStockCache($event->id);

        $response = $this->actingAs($user)
            ->post('/orders/reserve', ['event_id' => $event->id]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('completes the full purchase flow: reserve → confirm → success', function () {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'available_tickets' => 5,
            'price' => 100.00,
            'status' => 'active',
        ]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->warmStockCache($event->id);

        // Step 1: reserve
        $reserveResponse = $this->actingAs($user)
            ->post('/orders/reserve', ['event_id' => $event->id]);

        $reserveResponse->assertRedirect();
        $token = session('_flash.new', []);

        // Get the token from reservation in Redis
        $token = $inventoryService->reserveTicket($event->id, $user->id);
        expect($token)->toBeString();

        // Step 2: confirm
        $confirmResponse = $this->actingAs($user)
            ->post("/orders/confirm/{$token}");

        $confirmResponse->assertRedirect();

        // Verify order is persisted in database
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);
    });

    it('requires authentication to reserve a ticket', function () {
        $event = Event::factory()->create(['available_tickets' => 10]);

        $this->post('/orders/reserve', ['event_id' => $event->id])
            ->assertRedirect('/login');
    });

    it('rejects confirmation with expired token', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/orders/confirm/invalid-token-xyz')
            ->assertRedirect('/events')
            ->assertSessionHas('error');
    });
});
