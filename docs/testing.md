# Testing

## Package validation

```bash
composer test
```

This runs:

- PHPStan
- Pint in check mode
- Pest type coverage (100% minimum)
- Pest tests in parallel

Individual commands:

```bash
composer analyse
composer lint
composer lint:check
composer test:types
composer test:unit
```

## Supported matrix

CI covers the following PHP and Laravel combinations:

| PHP | Laravel | Testbench |
| --- | --- | --- |
| 8.2 | 10, 11, 12 | 8, 9, 10 |
| 8.3 | 10, 11, 12, 13 | 8, 9, 10, 11 |
| 8.4 | 11, 12, 13 | 9, 10, 11 |
| 8.5 | 12, 13 | 10, 11 |

Both `prefer-lowest` and `prefer-stable` dependency lanes run on Ubuntu. Windows runs one stable lane per Laravel major.

Laravel 10 and 11 are end-of-life; their CI lanes are best-effort compatibility checks.

## Workbench

Build and serve the local demo:

```bash
composer build
composer serve
```

The workbench routes are:

```text
/demo-table?mode=paginate
/demo-table?mode=simple
/demo-table?mode=cursor
/demo-tables
```

`/demo-tables` renders two independent tables on one page.

## Testing a table

Package tests use Pest and Orchestra Testbench. The shared `tests/TestCase.php`
sets up an in-memory SQLite database.

```php
$request = Request::create('/items', 'GET', ['search' => 'alpha']);

$table = HtmxTable::make(DB::table('items'))
    ->columns([Column::make('name')->searchable()])
    ->paginate(10);

$html = $table->render($request)->render();

expect($html)->toContain('Alpha');
```

## Testing HTMX requests

The package does not check `HX-Request`; that is the host application's
responsibility. Test your route with and without the header:

```php
$this->get('/users', ['HX-Request' => 'true'])
    ->assertOk()
    ->assertSee('htmx-table-wrapper');
```

## Type coverage

New public code must keep the package at 100% type coverage:

```bash
composer test:types
```

Class constants are untyped for PHP 8.2 compatibility and use targeted
`@pest-ignore-type` markers. All other public types must remain at 100%.

Next: [Troubleshooting](troubleshooting.md).
