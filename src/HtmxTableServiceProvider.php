<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class HtmxTableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/htmx-table.php',
            'htmx-table',
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'htmx-table');

        Blade::anonymousComponentNamespace('htmx-table::components', 'htmx-table');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/htmx-table.php' => config_path('htmx-table.php'),
        ], ['htmx-table', 'htmx-table-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/htmx-table'),
        ], ['htmx-table', 'htmx-table-views']);

        $this->publishes([
            __DIR__.'/../resources/css/htmx-table.css' => public_path('vendor/htmx-table/htmx-table.css'),
        ], ['htmx-table', 'htmx-table-assets']);
    }
}
