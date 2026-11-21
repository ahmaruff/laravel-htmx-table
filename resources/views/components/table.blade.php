@props([
    'rows',
    'columns',
    'paginationMode',
    'perPage' => 15,
    'state' => [
        'search' => '',
        'sort' => '',
        'dir' => 'asc',
        'page' => null,
        'cursor' => null,
    ],
])

<div {{ $attributes }}>
    @include('htmx-table::table', [
        'rows' => $rows,
        'columns' => $columns,
        'paginationMode' => $paginationMode,
        'perPage' => $perPage,
        'state' => $state,
    ])
</div>