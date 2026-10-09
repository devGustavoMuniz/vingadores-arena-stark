<?php

namespace Database\Seeders;

use App\Inventory\Models\Event;
use App\Inventory\Services\InventoryService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $events = [
            [
                'name' => 'Rock in Rio — Arena Stark Edition',
                'description' => 'O maior festival de música do Brasil acontece na Arena Stark. Artistas nacionais e internacionais em uma noite inesquecível.',
                'venue' => 'Arena Stark, Belo Horizonte — MG',
                'starts_at' => now()->addWeeks(4),
                'total_tickets' => 200,
                'available_tickets' => 200,
                'price' => 280.00,
                'status' => 'active',
            ],
            [
                'name' => 'Stand-Up Comedy Night',
                'description' => 'Uma noite de humor com os melhores comediantes do país. Risadas garantidas!',
                'venue' => 'Teatro Municipal, São Paulo — SP',
                'starts_at' => now()->addWeeks(2),
                'total_tickets' => 100,
                'available_tickets' => 45,
                'price' => 120.00,
                'status' => 'active',
            ],
            [
                'name' => 'Tech Summit 2026',
                'description' => 'Conferência de tecnologia com palestras sobre IA, cloud e desenvolvimento de software.',
                'venue' => 'Centro de Convenções, Curitiba — PR',
                'starts_at' => now()->addMonths(2),
                'total_tickets' => 500,
                'available_tickets' => 320,
                'price' => 450.00,
                'status' => 'active',
            ],
        ];

        $inventoryService = app(InventoryService::class);

        foreach ($events as $eventData) {
            $event = Event::firstOrCreate(['name' => $eventData['name']], $eventData);
            // Warm Redis cache with initial stock
            $inventoryService->warmStockCache($event->id);
        }
    }
}
