# Installation

## Requirements

- PHP 8.3 or newer
- Laravel 12 or 13
- HTMX 1.9 or newer (2.x recommended)

The package uses HTMX's `closest` selector, so HTMX 1.9+ is required.

## Install the package

```bash
composer require ahmaruff/laravel-htmx-table
```

Laravel discovers `Ahmaruff\HtmxTable\HtmxTableServiceProvider` automatically.

## Publish resources

Publish everything:

```bash
php artisan vendor:publish --tag="htmx-table"
```

Or publish individual resources:

```bash
php artisan vendor:publish --tag="htmx-table-config"
php artisan vendor:publish --tag="htmx-table-views"
php artisan vendor:publish --tag="htmx-table-assets"
```

The assets tag publishes the stylesheet to:

```text
public/vendor/htmx-table/htmx-table.css
```

## Add HTMX and the stylesheet

```blade
<link rel="stylesheet" href="{{ asset('vendor/htmx-table/htmx-table.css') }}">
<script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js"></script>
```

The package does not bundle HTMX. Use the CDN or your application's asset pipeline.

## Verify the installation

```bash
php artisan about
```

The package config is merged from `config/htmx-table.php` and the `htmx-table`
view namespace is loaded. If you published views, they live under
`resources/views/vendor/htmx-table`.

Next: [Quickstart](quickstart.md).
