# Quickstart

## 1. Build the table

```php
use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use App\Models\User;

$table = HtmxTable::make(User::query())
    ->columns([
        Column::make('name')->searchable()->sortable(),
        Column::make('email')->searchable()->sortable(),
        Column::make('created_at')->label('Joined')->sortable(),
    ])
    ->paginate(15);
```

## 2. Return a fragment for HTMX requests

```php
use Illuminate\Http\Request;

public function index(Request $request)
{
    $table = HtmxTable::make(User::query())
        ->columns([
            Column::make('name')->searchable()->sortable(),
            Column::make('email')->searchable()->sortable(),
        ])
        ->paginate(15);

    if ($request->header('HX-Request')) {
        return $table->render($request);
    }

    return view('users.index', ['table' => $table]);
}
```

The package does not inspect `HX-Request` itself. The host application decides
whether a request receives the table fragment or a full page.

## 3. Render the table

```blade
{{-- resources/views/users/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ asset('vendor/htmx-table/htmx-table.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js"></script>
</head>
<body>
    <x-htmx-table::table :table="$table" />
</body>
</html>
```

You can also render the view directly:

```blade
{{ $table->render() }}
```

## How the interaction works

- The search input, sort links, and pagination links issue HTMX `GET` requests.
- The wrapper owns `hx-target="closest .htmx-table-wrapper"` and
  `hx-swap="outerHTML"`, so each trigger replaces its own table fragment.
- The controller returns `$table->render($request)` for HTMX requests.
- Search, sort, and pagination state travel in the query string.

Next: [Columns](columns.md).
