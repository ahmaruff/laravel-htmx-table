# Customization

## Publish views and CSS

```bash
php artisan vendor:publish --tag="htmx-table-views"
php artisan vendor:publish --tag="htmx-table-assets"
```

Published views live in `resources/views/vendor/htmx-table`. Laravel resolves
them before the package views. The stylesheet is plain CSS with `--htmx-*`
custom properties, so you can override colors, spacing, and radii without
touching the markup.

Use `classes()` for per-table styling without copying the view:

```php
$table->classes('table-compact', 'theme-dark');
```

```css
.theme-dark {
    --htmx-bg: #111827;
    --htmx-text: #e5e7eb;
}
```

For structural changes, copy the published view and pass it with `view()`. See
[Custom views](custom-views.md); `view()` is an escape hatch where the custom
view owns the markup contract.

## Search focus and overflow

The search input sends the `X-Htmx-Table-Search` header with search requests.
When the server sees that header, the search input is rendered with `autofocus`,
and the wrapper gets a conditional `hx-on::load` handler that moves the caret to
the end after focus. A custom view must keep both behaviors.

The table is wrapped in `.htmx-table-scroll`, which handles horizontal
overflow while keeping the toolbar and pagination outside the scroll area. Set
`--htmx-table-scroll-max-height` to make the table scroll vertically too. Add
the `is-sticky` class for sticky toolbar/pagination when the page itself
scrolls.

## Table id

The table wrapper has no id by default. Add one when CSS, JavaScript, or a
trigger outside the fragment needs a stable selector:

```php
HtmxTable::make($query)->id('users-table');
```

```blade
<button hx-get="/users" hx-target="#users-table">Refresh users</button>
```

Ids must be unique on the page.

## Multiple tables

Each fragment targets itself with:

```blade
hx-target="closest .htmx-table-wrapper"
```

So multiple tables work without ids:

```blade
<x-htmx-table::table :table="$users" />
<x-htmx-table::table :table="$orders" />
```

## Rendering options

Use the Blade component:

```blade
<x-htmx-table::table :table="$table" />
```

Or render the view directly:

```blade
{{ $table->render() }}
```

If you need a specific request instance:

```php
return $table->render($request);
```

## Search debounce

The search input uses:

```blade
hx-trigger="input changed delay:600ms, search"
```

Publish the views to change the debounce delay or the toolbar markup.

## HTMX attributes

The wrapper owns `hx-target` and `hx-swap`; they are inherited by the search
input, sort links, pagination links, and the per-page selector. If you customize
the markup, keep the trigger inside the wrapper or set an explicit target, and
preserve the search focus attributes if you need refocus behavior.

Next: [Configuration](configuration.md).
