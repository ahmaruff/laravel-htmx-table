<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Support\ServiceProvider;

class HtmxTableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-htmx-table.php', 'laravel-htmx-table');

        $this->app->singleton(HtmxTable::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-htmx-table');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-htmx-table.php' => config_path('laravel-htmx-table.php'),
        ], ['laravel-htmx-table', 'laravel-htmx-table-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-htmx-table'),
        ], ['laravel-htmx-table', 'laravel-htmx-table-views']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-htmx-table'),
        ], ['laravel-htmx-table', 'laravel-htmx-table-assets']);
    }
}
