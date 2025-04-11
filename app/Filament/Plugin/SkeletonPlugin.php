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
        // ...
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
