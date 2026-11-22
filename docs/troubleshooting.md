# Troubleshooting

## The table does not update

- Confirm HTMX is loaded on the page.
- Confirm your route returns the table fragment for normal HTMX requests:

  ```php
  if (HtmxTable::isFragmentRequest($request)) {
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
php artisan vendor:publish --tag="htmx-table-lang"
```

## Multiple tables conflict

Give each table its own wrapper (the package does this automatically) and avoid
duplicate ids. If you need external triggers, set unique ids:

```php
HtmxTable::make($query)->id('users-table');
```

## Back/forward shows only the table fragment

HTMX history-restore requests can include both `HX-Request` and
`HX-History-Restore-Request`. Return a full page for history restore:

```php
if (HtmxTable::isFragmentRequest($request)) {
    return $table->render($request);
}

return view('users.index', ['table' => $table]);
```

Also keep `hx-push-url` on the real page URL, not a fragment-only endpoint, and
make sure a custom `hx-history-elt` includes the page layout around the table.

## Search input loses focus after a swap

The default view sends the `X-Htmx-Table-Search` header with search requests,
renders the search input with `autofocus`, and moves the caret to the end with a
conditional `hx-on::load` handler. A custom view must keep these behaviors. If
you use your own search trigger, send the same header or set a stable input id
and rely on HTMX focus restoration.

## Toolbar or pagination scrolls away on wide tables

The table is inside `.htmx-table-scroll`; the toolbar and pagination are outside
it. If you use a custom view, keep that structure. For vertical scrolling, set
`--htmx-table-scroll-max-height` or add the `is-sticky` wrapper class.

## Language or placeholder does not change

- Check `app()->getLocale()` or the per-table `locale()` value.
- Use `searchPlaceholder()` for a per-table placeholder.
- Publish `--tag="htmx-table-lang"` before editing translations.
- Clear caches with `php artisan optimize:clear`.

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
