<?php

namespace Modules\Submissions\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class SubmissionsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Submissions';
    protected string $nameLower = 'submissions';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        if (class_exists('Breadcrumbs') || class_exists(\Diglactic\Breadcrumbs\Breadcrumbs::class)) {
            if (file_exists(module_path($this->name, 'routes/breadcrumbs.php'))) {
                require module_path($this->name, 'routes/breadcrumbs.php');
            }
        }
    }
}
