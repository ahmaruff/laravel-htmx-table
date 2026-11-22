# Pagination

Choose one mode before rendering:

```php
$table->paginate(15);         // length-aware pagination
$table->simplePaginate(15);   // previous/next only
$table->cursorPaginate(15);   // cursor pagination
```

The mode is required. Rendering without one throws
`Ahmaruff\HtmxTable\Exceptions\MissingPaginationModeException`.

## Per-page size

The default comes from `config/htmx-table.php`:

```php
'per_page' => 15,
```

Passing a value to the pagination method overrides it for that table:

```php
$table->paginate(50);
```

## Pagination state

`paginate()` and `simplePaginate()` use the configured `page` parameter.
`cursorPaginate()` uses the configured `cursor` parameter.

The table view renders previous/next links from the Laravel paginator and keeps
the current search and sort state. Length-aware pagination also shows the
current and last page.

## Cursor pagination

Cursor pagination requires a deterministic order. The package always adds a
tie-breaker:

- the Eloquent model's key column for Eloquent builders
- `id` for query-builder sources

When the requested sort column is not already the tie-breaker, the tie-breaker
is added as a secondary order. Without a requested sort, cursor mode orders by
the tie-breaker ascending.

An invalid or expired cursor restarts from the first page.

## Page size and cursor mode

Cursor pagination does not support page numbers. Do not reuse a `page`
parameter with cursor mode. The package resets page/cursor state when search or
sort changes.

Next: [Customization](customization.md).
