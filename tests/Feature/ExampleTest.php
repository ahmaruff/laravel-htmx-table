<?php

declare(strict_types=1);

use Ahmaruff\HtmxTable\HtmxTable;

it('resolves the singleton', function () {
    expect(app(HtmxTable::class))->toBeInstanceOf(HtmxTable::class);
});

it('returns the same instance from the container', function () {
    expect(app(HtmxTable::class))->toBe(app(HtmxTable::class));
});

it('merges the package config', function () {
    expect(config('laravel-htmx-table.placeholder'))->toBe('default');
});

it('loads the package views', function () {
    expect(view()->exists('laravel-htmx-table::placeholder'))->toBeTrue();
});
