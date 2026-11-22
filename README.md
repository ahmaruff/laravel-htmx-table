<div align="center">
    <h1>Laravel Htmx Table</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/ahmaruff/laravel-htmx-table"><img src="https://img.shields.io/packagist/v/ahmaruff/laravel-htmx-table.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/ahmaruff/laravel-htmx-table"><img src="https://img.shields.io/packagist/php-v/ahmaruff/laravel-htmx-table.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/ahmaruff/laravel-htmx-table"><img src="https://badge.laravel.cloud/badge/ahmaruff/laravel-htmx-table?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/ahmaruff/laravel-htmx-table/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/ahmaruff/laravel-htmx-table/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/ahmaruff/laravel-htmx-table"><img src="https://img.shields.io/packagist/dt/ahmaruff/laravel-htmx-table.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A lightweight server-driven table for Laravel using Blade and HTMX.

The package renders the table fragment on the server. Search, sorting, and pagination are normal Laravel query work; HTMX swaps the returned fragment into the page.

## Requirements

- PHP 8.2–8.5
- Laravel 10–13
- HTMX 1.9+ (2.x recommended; the package uses `closest` targeting)

### Supported versions

| PHP | Laravel |
| --- | --- |
| 8.2 | 10, 11, 12 |
| 8.3 | 10, 11, 12, 13 |
| 8.4 | 11, 12, 13 |
| 8.5 | 12, 13 |

Laravel 10 and 11 are end-of-life; support for them is best-effort. PHP 8.6 is not supported yet. Composer may block end-of-life Laravel versions because of known security advisories.

## Installation

```bash
composer require ahmaruff/laravel-htmx-table
```

Publish everything at once:

```bash
php artisan vendor:publish --tag="htmx-table"
```

Or publish individual resources:

```bash
php artisan vendor:publish --tag="htmx-table-config"
php artisan vendor:publish --tag="htmx-table-views"
php artisan vendor:publish --tag="htmx-table-assets"
php artisan vendor:publish --tag="htmx-table-lang"
```

The stylesheet is published to `public/vendor/htmx-table/htmx-table.css`.
Translations are published to `lang/vendor/htmx-table`.

## Setup

Include HTMX and the published stylesheet in the page that hosts the table:

```blade
<link rel="stylesheet" href="{{ asset('vendor/htmx-table/htmx-table.css') }}">
<script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js"></script>
```

## Quickstart

```php
use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use App\Models\User;
use Illuminate\Http\Request;

public function index(Request $request)
{
    $table = HtmxTable::make(User::query())
        ->columns([
            Column::make('name')->searchable()->sortable(),
            Column::make('email')->searchable()->sortable(),
            Column::make('created_at')->label('Joined')->sortable(),
        ])
        ->paginate(15);

    if (HtmxTable::isFragmentRequest($request)) {
        return $table->render($request);
    }

    return view('users.index', ['table' => $table]);
}
```

`isFragmentRequest()` treats normal HTMX requests as fragments, but keeps
history-restore requests as full-page requests so browser back/forward does not
replace the page with a table fragment alone.

```blade
{{-- resources/views/users/index.blade.php --}}
<x-htmx-table::table :table="$table" />
```

The package does **not** decide when to return a fragment. The host application checks `HtmxTable::isFragmentRequest($request)` (or any other condition) and returns `$table->render($request)` for HTMX requests. The same table can be rendered directly with `{{ $table->render() }}`.

## Columns

```php
Column::make('name')          // public key and default data key
    ->label('Full name')      // optional display label
    ->dataKey('users.name')   // optional database/row access path
    ->searchable()            // include in search
    ->sortable();             // allow sorting by this column
```

The `key` is the stable identifier used in sort state and URLs. The `dataKey` is used for search, ordering, and reading the cell value. Dotted data keys require a join for search and sort; they do not add relation query support automatically.

### Cell rendering

Cells are escaped by default. A renderer can return an `Htmlable` value when it intentionally produces markup:

```php
use Illuminate\Support\HtmlString;

Column::make('status')->render(
    fn (mixed $row, mixed $value): HtmlString => new HtmlString(
        '<span class="badge">'.e($value).'</span>'
    ),
);
```

Returning a plain string containing HTML renders it escaped. See [Security](docs/security.md).

## Search and sorting

Search is a grouped `OR` of `LIKE` conditions across columns marked `searchable()`. Sorting only accepts columns marked `sortable()`; unknown sort keys and non-sortable columns are ignored, and invalid directions fall back to `asc`.

Query parameter names are configurable in `config/htmx-table.php`:

```php
return [
    'per_page' => 15,

    'query_params' => [
        'search' => 'search',
        'sort' => 'sort',
        'dir' => 'dir',
        'page' => 'page',
        'cursor' => 'cursor',
        'per_page' => 'per_page',
    ],

    'pagination' => [
        'on_each_side' => 2,
    ],
];
```

Generated URLs, the search input, and the per-page selector use the same aliases as request parsing. Query parameters unrelated to table state are preserved.

## Pagination

```php
$table->paginate(15);         // length-aware pagination with page numbers
$table->simplePaginate(15);   // previous/next only
$table->cursorPaginate(15);   // cursor pagination
```

`paginate()` renders numbered page links with ellipses. The number of pages
around the current page is controlled by `pagination.on_each_side`.

Enable a per-page selector explicitly:

```php
$table->perPageOptions([10, 15, 25, 50])->paginate(15);
```

The selector uses the configured `per_page` query parameter, validates the
request against the allowed options, and resets the current page when changed.
The table default is always included in the selector.

The mode must be selected before rendering. Cursor pagination always applies a deterministic tie-breaker (the model key, or `id` for query-builder sources). An invalid or expired cursor restarts from the first page.

## Customization

Use `classes()` for per-table styling and CSS variables for colors, spacing, and
radii:

```php
$table->classes('table-compact', 'theme-dark');
```

For structural changes, use `view()` with a copied published view. `view()` is
an escape hatch: the custom view owns the markup contract, including the
`htmx-table-wrapper` target and swap attributes. See
[Custom views](docs/custom-views.md).

The table lives in a `.htmx-table-scroll` container, so the toolbar and
pagination stay visible when the table overflows horizontally.

## Localization

The package ships English and Indonesian translations. Use the application
locale or override it per table:

```php
$table->locale('id');
$table->searchPlaceholder('Cari user...');
```

Publish the language files to add a locale or change copy:

```bash
php artisan vendor:publish --tag="htmx-table-lang"
```

See [Localization](docs/localization.md).

## Multiple tables

Each fragment targets itself relative to the trigger:

```blade
hx-target="closest .htmx-table-wrapper"
```

That means multiple tables can share a page without ids:

```blade
<x-htmx-table::table :table="$users" />
<x-htmx-table::table :table="$orders" />
```

An id is optional and only needed for CSS, JavaScript, or triggers outside the table fragment:

```php
HtmxTable::make($query)->id('users-table');
```

Then target it explicitly:

```blade
<button hx-get="/users" hx-target="#users-table">Refresh users</button>
```

## Documentation

- [Installation](docs/installation.md)
- [Quickstart](docs/quickstart.md)
- [Columns](docs/columns.md)
- [Searching and sorting](docs/searching-sorting.md)
- [Pagination](docs/pagination.md)
- [Customization](docs/customization.md)
- [Custom views](docs/custom-views.md)
- [Localization](docs/localization.md)
- [Configuration](docs/configuration.md)
- [Security](docs/security.md)
- [Testing](docs/testing.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Domain glossary](GLOSSARY.md)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Htmx Table! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Ahmad Ma'ruf](https://github.com/ahmaruff)
- [All Contributors](../../contributors)

## License

Laravel Htmx Table is open-sourced software licensed under the [MIT license](LICENSE.md).
