# Custom views

`view()` is an escape hatch for structural changes. Prefer `classes()` and CSS
variables when you only need a different visual style. See
[Customization](customization.md).

A custom view owns the markup contract. The package guarantees the data passed
to the view, but it does not validate the rendered HTML.

## Start from the published view

```bash
php artisan vendor:publish --tag="htmx-table-views"
```

Copy the published `table.blade.php` into your application and edit the copy:

```php
$table->view('tables.compact');
```

The default package views also expose reusable partials:

```blade
@include('htmx-table::partials.toolbar')
@include('htmx-table::partials.pagination')
```

## View data contract

A custom view receives:

- `$rows` — the paginator or cursor paginator
- `$columns` — the configured `Column` objects
- `$paginationMode` — `paginate`, `simple-paginate`, or `cursor`
- `$state` — the normalized `TableState`
- `$id` — the optional table id
- `$classes` — custom wrapper classes from `classes()`
- `$perPage` — the effective page size
- `$perPageOptions` — the effective per-page option list
- `$locale` — the effective table locale
- `$focusSearch` — whether the current response should autofocus the search input
- `$searchPlaceholder` — the effective search placeholder

## DOM and HTMX contract

A custom view must keep the behavior it needs:

- The root fragment has `.htmx-table-wrapper`.
- The root has `hx-target="closest .htmx-table-wrapper"` and
  `hx-swap="outerHTML"`.
- The search input uses `$state->alias('search')`, the `.htmx-table-search`
  class, the generated `$state` URL, the `X-Htmx-Table-Search` request header,
  and a conditional `autofocus` attribute for search responses.
- The wrapper keeps the conditional `hx-on::load` handler that places the caret
  at the end of the search value.
- Sort and pagination links use URLs from `$state`.
- The table is wrapped in `.htmx-table-scroll` for horizontal overflow.
- The search refocus and caret-position attributes are preserved, or an
  equivalent focus strategy is added.
- The per-page selector uses `$state->alias('per_page')` when enabled.

Omitting these attributes can break multiple-table targeting, state
preservation, refocus, or overflow behavior.

## Upgrade warning

Custom views do not receive package updates automatically. After upgrading,
compare your view with the latest published view and merge new data or markup
requirements.

A custom view that hardcodes the search markup without the header, `autofocus`,
and caret handler will not restore focus correctly.
