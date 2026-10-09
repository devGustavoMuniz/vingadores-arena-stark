<?php

namespace App\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Inventory\Models\Event;
use App\Inventory\Services\InventoryService;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * Lists all active events with cached stock information.
     */
    public function index(): Response
    {
        $events = Event::where('status', 'active')
            ->orderBy('starts_at')
            ->get()
            ->map(function (Event $event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'venue' => $event->venue,
                    'starts_at' => $event->starts_at->toIso8601String(),
                    'price' => $event->price,
                    'available_tickets' => $this->inventoryService->getAvailableStock($event->id),
                    'is_available' => $event->isAvailable(),
                ];
            });

        return Inertia::render('events/index', [
            'events' => $events,
        ]);
    }

    /**
     * Shows event detail with current stock from Redis (cache-aside).
     */
    public function show(int $id): Response
    {
        $event = Event::findOrFail($id);
        $availableStock = $this->inventoryService->getAvailableStock($id);

        return Inertia::render('events/show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'venue' => $event->venue,
                'starts_at' => $event->starts_at->toIso8601String(),
                'price' => $event->price,
                'available_tickets' => $availableStock,
                'is_available' => $availableStock > 0 && $event->status === 'active',
            ],
        ]);
    }
}
