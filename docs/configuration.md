# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="htmx-table-config"
```

The published file lives at `config/htmx-table.php`.

```php
return [
    'per_page' => 15,

    'query_params' => [
        'search' => 'search',
        'sort' => 'sort',
        'dir' => 'dir',
        'page' => 'page',
        'cursor' => 'cursor',
    ],
];
```

## `per_page`

The default page size for `paginate()`, `simplePaginate()`, and
`cursorPaginate()` when no explicit value is passed. Non-positive or non-integer
values fall back to `15`.

## `query_params`

Maps table-state fields to request keys. The same aliases are used for request
parsing, the search input's `name`, and generated URLs.

Keep aliases unique. Empty values fall back to the defaults above.

If you use configuration caching, rebuild the cache after changing these values:

```bash
php artisan config:clear
php artisan config:cache
```

Next: [Security](security.md).
