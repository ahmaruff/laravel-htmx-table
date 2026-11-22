# Pagination

Choose one mode before rendering:

```php
$table->paginate(15);         // length-aware pagination with page numbers
$table->simplePaginate(15);   // previous/next only
$table->cursorPaginate(15);   // cursor pagination
```

The mode is required. Rendering without one throws
`Ahmaruff\HtmxTable\Exceptions\MissingPaginationModeException`.

## Page numbers

`paginate()` renders numbered page links using Laravel's `UrlWindow`. The current
page is marked with `aria-current="page"`, and distant pages are separated with
an ellipsis.

Configure how many pages appear on each side of the current page:

```php
'pagination' => [
    'on_each_side' => 2,
],
```

`simplePaginate()` and `cursorPaginate()` do not render page numbers because
they do not expose a last page to jump to.

## Per-page size

The default comes from `config/htmx-table.php`:

```php
'per_page' => 15,
```

Passing a value to the pagination method overrides it for that table:

```php
$table->paginate(50);
```

Enable a per-page selector explicitly:

```php
$table->perPageOptions([10, 25, 50])->paginate(15);
```

The selector is only rendered when options are provided. It uses the `per_page`
query parameter, ignores values outside the allowed list, and resets the current
page/cursor when changed. The table default is always included in the selector,
even when it is not one of the passed options.

## Pagination state

`paginate()` and `simplePaginate()` use the configured `page` parameter.
`cursorPaginate()` uses the configured `cursor` parameter.

The table view renders previous/next links and page numbers from the Laravel
paginator and keeps the current search, sort, and per-page state. Length-aware
pagination also shows the current and last page.

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
parameter with cursor mode. The package resets page/cursor state when search,
sort, or per-page changes.

Next: [Customization](customization.md).
