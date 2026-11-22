# Troubleshooting

## The table does not update

- Confirm HTMX is loaded on the page.
- Confirm your route returns the table fragment when `HX-Request` is present:

  ```php
  if ($request->header('HX-Request')) {
      return $table->render($request);
  }
  ```

- Confirm the trigger is inside the table wrapper. The wrapper uses
  `hx-target="closest .htmx-table-wrapper"`; triggers outside the wrapper need
  their own explicit target or a table id.

## `vendor:publish` finds nothing

Use the package tags:

```bash
php artisan vendor:publish --tag="htmx-table"
php artisan vendor:publish --tag="htmx-table-config"
php artisan vendor:publish --tag="htmx-table-views"
php artisan vendor:publish --tag="htmx-table-assets"
```

## Multiple tables conflict

Give each table its own wrapper (the package does this automatically) and avoid
duplicate ids. If you need external triggers, set unique ids:

```php
HtmxTable::make($query)->id('users-table');
```

## Search or sort state disappears

If you use custom query-parameter aliases, make sure the search input and
generated URLs use the same aliases. The package does this automatically from
`config/htmx-table.php`. Clear the config cache after changing aliases:

```bash
php artisan config:clear
```

## Cursor pages overlap

Cursor pagination needs deterministic ordering. The package adds the model key
(or `id`) as a tie-breaker. Do not manually order by a non-unique column without
a tie-breaker.

## CSS is missing

Publish the assets and reference the published file:

```bash
php artisan vendor:publish --tag="htmx-table-assets"
```

```blade
<link rel="stylesheet" href="{{ asset('vendor/htmx-table/htmx-table.css') }}">
```

## The Blade component is not found

Clear compiled views:

```bash
php artisan view:clear
```

The component is registered as `x-htmx-table::table`.

## HTMX relative targeting does not work

The package requires HTMX 1.9+ for `closest` targeting. Upgrade HTMX or provide
an explicit table id and `hx-target`.
