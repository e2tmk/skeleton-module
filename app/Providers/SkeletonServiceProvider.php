<?php

declare(strict_types = 1);

namespace Modules\Skeleton\Providers;

use Illuminate\Support\ServiceProvider;
use ModuleManager\ModuleManager\Concerns\HasServiceProviderMethods;
use Nwidart\Modules\Traits\PathNamespace;

class SkeletonServiceProvider extends ServiceProvider
{
    use HasServiceProviderMethods;
    use PathNamespace;

    protected string $name = 'Skeleton';

    protected string $nameLower = 'skeleton';

    public function boot(): void
    {
        $this->bootConfigViewTranslationAndMigrations();
    }

    #[\Override]
    public function register(): void
    {
        // ...
    }
}
