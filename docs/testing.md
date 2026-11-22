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

Next: [Troubleshooting](troubleshooting.md).
