<?php

declare(strict_types=1);

use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\TableState;
use Illuminate\Http\Request;

it('parses state with configured aliases and preserves unrelated query params', function () {
    config()->set('htmx-table.query_params', [
        'search' => 'q',
        'sort' => 'order_by',
        'dir' => 'order_dir',
        'page' => 'p',
        'cursor' => 'c',
    ]);

    $request = Request::create('/items', 'GET', [
        'q' => ' needle ',
        'order_by' => 'name',
        'order_dir' => 'DESC',
        'p' => '2',
        'filter' => 'active',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_PAGINATE);

    expect($state->search)->toBe('needle')
        ->and($state->sort)->toBe('name')
        ->and($state->dir)->toBe('desc')
        ->and($state->page)->toBe(2)
        ->and($state->cursor)->toBeNull()
        ->and($state->alias('search'))->toBe('q')
        ->and($state->url('/items'))->toContain('filter=active')
        ->and($state->url('/items'))->toContain('q=needle')
        ->and($state->url('/items'))->toContain('order_by=name')
        ->and($state->url('/items'))->toContain('order_dir=desc')
        ->and($state->url('/items'))->not->toContain('p=2');
});

it('normalizes invalid directions and pages', function () {
    $request = Request::create('/items', 'GET', [
        'dir' => 'sideways',
        'page' => '0',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_PAGINATE);

    expect($state->dir)->toBe('asc')
        ->and($state->page)->toBe(1);
});

it('parses cursor state without a page', function () {
    $request = Request::create('/items', 'GET', [
        'cursor' => ' encoded-cursor ',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_CURSOR);

    expect($state->cursor)->toBe('encoded-cursor')
        ->and($state->page)->toBeNull();
});

it('builds urls that reset page and cursor while preserving current search and sort', function () {
    $request = Request::create('/items', 'GET', [
        'search' => 'alpha',
        'sort' => 'name',
        'dir' => 'asc',
        'page' => '3',
        'filter' => 'active',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_PAGINATE);
    $url = $state->url('/items', ['sort' => 'status', 'dir' => 'desc']);

    expect($url)->toContain('filter=active')
        ->and($url)->toContain('search=alpha')
        ->and($url)->toContain('sort=status')
        ->and($url)->toContain('dir=desc')
        ->and($url)->not->toContain('page=3')
        ->and($url)->not->toContain('cursor=');
});

it('applies state to paginator urls without losing their page or cursor', function () {
    $request = Request::create('/items', 'GET', [
        'search' => 'alpha',
        'sort' => 'name',
        'dir' => 'asc',
        'filter' => 'active',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_PAGINATE);
    $url = $state->applyToUrl('https://example.test/items?page=2&sort=old&dir=desc');

    expect($url)->toContain('page=2')
        ->and($url)->toContain('filter=active')
        ->and($url)->toContain('search=alpha')
        ->and($url)->toContain('sort=name')
        ->and($url)->toContain('dir=asc')
        ->and($url)->not->toContain('sort=old')
        ->and($url)->not->toContain('dir=desc');
});

it('applies state to cursor paginator urls without losing the paginator cursor', function () {
    $request = Request::create('/items', 'GET', [
        'search' => 'alpha',
        'cursor' => 'current-cursor',
        'filter' => 'active',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_CURSOR);
    $url = $state->applyToUrl('https://example.test/items?cursor=next-cursor');

    expect($url)->toContain('cursor=next-cursor')
        ->and($url)->toContain('search=alpha')
        ->and($url)->toContain('filter=active')
        ->and($url)->not->toContain('current-cursor');
});

it('preserves nested query params when applying state to paginator urls', function () {
    $request = Request::create('/items', 'GET', [
        'search' => 'alpha',
    ]);

    $state = TableState::fromRequest($request, HtmxTable::PAG_MODE_PAGINATE);
    $url = $state->applyToUrl('https://example.test/items?page=2&filter%5Bstatus%5D=active');

    expect($url)->toContain('page=2')
        ->and($url)->toContain('search=alpha')
        ->and($url)->toContain('filter%5Bstatus%5D=active');
});

it('falls back to default query param names when config values are empty', function () {
    config()->set('htmx-table.query_params.search', '');

    expect(TableState::queryParam('search', 'search'))->toBe('search');
});
