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
input, sort links, and pagination links. If you customize the markup, keep the
trigger inside the wrapper or set an explicit target.

Next: [Configuration](configuration.md).
