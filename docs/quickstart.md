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

    if (HtmxTable::isFragmentRequest($request)) {
        return $table->render($request);
    }

    return view('users.index', ['table' => $table]);
}
```

`isFragmentRequest()` only returns true for normal HTMX requests. History-restore
requests stay full-page requests so browser back/forward keeps the page layout.
The host application still decides what to return.

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

## History and back/forward

Keep the table on the same route as the full page. Do not push a fragment-only
endpoint with `hx-push-url`.

HTMX may request a URL from history with both `HX-Request` and
`HX-History-Restore-Request`. Returning a fragment for that request makes the
browser show only the table fragment on back/forward. `isFragmentRequest()`
excludes those restore requests.

You can also disable HTMX's history-restore-as-fragment behavior:

```blade
<meta name="htmx-config" content='{"historyRestoreAsHxRequest": false}'>
```

If you use a custom `hx-history-elt`, make sure it contains the table **and** the
surrounding page layout, not only the table wrapper.

## How the interaction works

- The search input, sort links, and pagination links issue HTMX `GET` requests.
- The wrapper owns `hx-target="closest .htmx-table-wrapper"` and
  `hx-swap="outerHTML"`, so each trigger replaces its own table fragment.
- The table is wrapped in `.htmx-table-scroll`; the toolbar and pagination stay
  outside the horizontal scroll area.
- The search input is refocused after a search request swaps the fragment.
- The controller returns `$table->render($request)` for HTMX requests.
- Search, sort, and pagination state travel in the query string.

Next: [Columns](columns.md).
