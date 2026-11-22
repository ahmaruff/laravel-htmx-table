<?php

declare(strict_types=1);

use Ahmaruff\HtmxTable\HtmxTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('merges the package config', function () {
    expect(config('htmx-table.per_page'))->toBe(15)
        ->and(config('htmx-table.query_params.search'))->toBe('search');
});

it('loads the package views', function () {
    expect(view()->exists('htmx-table::table'))->toBeTrue();
});

it('creates a table instance via make', function () {
    Schema::create('example_items', fn ($table) => $table->id());

    $table = HtmxTable::make(DB::table('example_items'));

    expect($table)->toBeInstanceOf(HtmxTable::class);
});
