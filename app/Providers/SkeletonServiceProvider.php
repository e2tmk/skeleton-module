<?php

declare(strict_types = 1);

namespace Modules\Skeleton\Providers;

use Illuminate\Support\ServiceProvider;
use ModuleManager\ModuleManager\Concerns\HasServiceProviderMethods;
use Modules\Skeleton\Console\Commands\ModuleBuildCommand;
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

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleBuildCommand::class,
            ]);
        }
    }

    #[\Override]
    public function register(): void
    {
        // ...
    }
}
