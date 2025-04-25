<?php

declare(strict_types = 1);

namespace Modules\Skeleton\Filament\Plugin;

use Filament\Contracts\Plugin;
use Filament\Events\ServingFilament;
use Filament\Panel;

class SkeletonPlugin implements Plugin
{
    public function getId(): string
    {
        return 'skeleton';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(in: module_path('Skeleton', 'app/Filament/Resources'), for: 'Modules\\Skeleton\\Filament\\Resources')
            ->discoverPages(in: module_path('Skeleton', 'app/Filament/Pages'), for: 'Modules\\Skeleton\\Filament\\Pages')
            ->discoverWidgets(in: module_path('Skeleton', 'app/Filament/Widgets'), for: 'Modules\\Skeleton\\Filament\\Widgets');
    }

    public function boot(Panel $panel): void
    {
        \Event::listen(ServingFilament::class, function () use ($panel): void {
            // ...
        });
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
