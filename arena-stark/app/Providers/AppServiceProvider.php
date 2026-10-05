<?php

namespace App\Providers;

use App\Inventory\Services\InventoryService;
use App\Notifications\Services\NotificationService;
use App\Orders\Services\OrderService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * Binds the modular context services as singletons in the DI container.
     */
    public function register(): void
    {
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(NotificationService::class);

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                inventoryService: $app->make(InventoryService::class),
                notificationService: $app->make(NotificationService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
