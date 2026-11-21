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
        ]);

    match ($mode) {
        HtmxTable::PAG_MODE_SIMPLE => $table->simplePaginate(5),
        HtmxTable::PAG_MODE_CURSOR => $table->cursorPaginate(5),
        default => $table->paginate(5),
    };

    if ($request->header('HX-Request')) {
        return $table->render($request);
    }

    return view('workbench-demo', [
        'tableHtml' => $table->render($request)->render(),
    ]);
});