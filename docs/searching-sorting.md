# Searching and sorting

## Search

Search is a grouped `OR` of `LIKE` conditions across columns marked
`searchable()`:

```php
$query->where(function ($query) {
    $query->where('name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%");
});
```

If no columns are searchable, the search term has no effect.

## Sorting

Sorting is a whitelist:

```php
Column::make('name')->sortable();
Column::make('email')->sortable();
```

A request with `sort=name&dir=desc` orders by the column's data key. An unknown
sort key or a non-sortable column is ignored. An invalid direction falls back to
`asc`.

Only one sort column is supported per table.

## Table state

The table state is:

- `search` — the trimmed search term
- `sort` — the public column key
- `dir` — `asc` or `desc`
- `page` — the page for `paginate()` / `simplePaginate()`
- `cursor` — the cursor for `cursorPaginate()`
- `per_page` — the selected page size when `perPageOptions()` is enabled

Search and sort links reset `page` and `cursor` so a new result set starts at
the beginning.

## Query-parameter aliases

Aliases are configured in `config/htmx-table.php`:

```php
'query_params' => [
    'search' => 'q',
    'sort' => 'order_by',
    'dir' => 'order_dir',
    'page' => 'p',
    'cursor' => 'c',
],
```

The same aliases are used for request parsing, the search input's `name`, the
per-page selector, and generated URLs. Keep the aliases unique.

## Preserved query parameters

Parameters unrelated to table state are preserved in search, sort, and
pagination URLs. For example, with `?filter=active&sort=name`:

```text
?filter=active&sort=name&dir=desc
```

The package does not collect extra form inputs automatically. If you add
external filters, include them in the request URL or use your own HTMX
`hx-include` configuration.

Next: [Pagination](pagination.md).
