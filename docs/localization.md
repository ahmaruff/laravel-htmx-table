# Localization

The package ships English (`en`) and Indonesian (`id`) translations for search,
empty state, pagination, per-page, and accessibility labels.

## Application locale

By default, table labels use `app()->getLocale()`:

```php
app()->setLocale('id');
```

## Per-table locale

Override the locale for one table when a page needs multiple languages:

```php
$table->locale('id');
```

Passing `null` or an empty string falls back to the application locale.
Missing translations fall back to Laravel's configured fallback locale.

## Search placeholder

The default placeholder comes from the translation key
`htmx-table::messages.search.placeholder`. Override it per table:

```php
$table->searchPlaceholder('Cari user...');
```

A `searchPlaceholder()` value wins over the translation key. Passing `null`
falls back to the translation, while an empty string is valid and renders
`placeholder=""`.

## Publish and add locales

```bash
php artisan vendor:publish --tag="htmx-table-lang"
```

The files live under `lang/vendor/htmx-table`. Add a new locale by creating
`lang/vendor/htmx-table/{locale}/messages.php` with the same keys as the shipped
files.

## Translation keys

```text
search.placeholder
search.label
empty
pagination.label
pagination.previous
pagination.next
pagination.page_of
pagination.page
pagination.per_page
pagination.go_to_page
```

## Custom views

A custom view must use the `htmx-table::messages.*` namespace, or the `$locale`
and `$searchPlaceholder` values passed to it. Hardcoded copy in a custom view
does not follow the table locale. See [Custom views](custom-views.md).

After publishing or changing language files, clear compiled/cached artifacts if
needed:

```bash
php artisan optimize:clear
```
