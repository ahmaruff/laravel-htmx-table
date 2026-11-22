<?php

declare(strict_types=1);

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo-table', function (Request $request) {
    $requestedMode = $request->query('mode');

    if ($requestedMode === null && $request->filled('cursor')) {
        $requestedMode = 'cursor';
    }

    $mode = match ($requestedMode ?? 'paginate') {
        'simple' => HtmxTable::PAG_MODE_SIMPLE,
        'cursor' => HtmxTable::PAG_MODE_CURSOR,
        default => HtmxTable::PAG_MODE_PAGINATE,
    };

    $table = HtmxTable::make(DB::table('items'))
        ->columns([
            Column::make('id')->sortable(),
            Column::make('name')->searchable()->sortable(),
            Column::make('status')->searchable()->sortable(),
            Column::make('sku')->searchable()->sortable(),
        ])
        ->perPageOptions([5, 10, 15, 25])
        ->locale($request->query('lang') === 'id' ? 'id' : null);

    match ($mode) {
        HtmxTable::PAG_MODE_SIMPLE => $table->simplePaginate(5),
        HtmxTable::PAG_MODE_CURSOR => $table->cursorPaginate(5),
        default => $table->paginate(5),
    };

    if (HtmxTable::isFragmentRequest($request)) {
        return $table->render($request);
    }

    return view('workbench-demo', [
        'tableHtml' => $table->render($request)->render(),
    ]);
});

Route::get('/demo-tables', function (Request $request) {
    $items = HtmxTable::make(DB::table('items'))
        ->columns([
            Column::make('id')->sortable(),
            Column::make('name')->searchable()->sortable(),
            Column::make('status')->searchable()->sortable(),
        ])
        ->paginate(5);

    $compact = HtmxTable::make(DB::table('items'))
        ->columns([
            Column::make('sku')->searchable()->sortable(),
            Column::make('status')->searchable()->sortable(),
        ])
        ->simplePaginate(3);

    if (HtmxTable::isFragmentRequest($request)) {
        $selected = $request->query('table', 'items');

        return $selected === 'compact'
            ? $compact->render($request)
            : $items->render($request);
    }

    $query = $request->query();

    return view('workbench-multiple', [
        'itemsTableHtml' => $items->render(
            Request::create('/demo-tables', 'GET', [...$query, 'table' => 'items']),
        )->render(),
        'compactTableHtml' => $compact->render(
            Request::create('/demo-tables', 'GET', [...$query, 'table' => 'compact']),
        )->render(),
    ]);
});
