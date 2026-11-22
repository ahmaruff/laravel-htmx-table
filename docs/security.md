# Security

## Cell output

Cell values are escaped by Blade by default. This is safe for normal database
values, including values that contain HTML:

```php
Column::make('name'); // <script> is escaped
```

A renderer may return an `Htmlable` value to opt in to raw markup:

```php
use Illuminate\Support\HtmlString;

Column::make('status')->render(
    fn (mixed $row, mixed $value): HtmlString => new HtmlString(
        '<span class="badge">'.e($value).'</span>'
    ),
);
```

Only return `Htmlable` output from code you trust. Plain strings returned by a
renderer are still escaped. Never concatenate untrusted input into raw HTML
without escaping it.

## Search and sort

Search and sort are whitelisted by column declarations:

- only `searchable()` columns participate in search
- only `sortable()` columns can be used for ordering
- sort directions are normalized to `asc` or `desc`

Request input cannot select an arbitrary database column.

## Query parameters

Table state travels in the query string. Search, sort, and pagination requests
are `GET` requests, so Laravel's CSRF protection is not involved. If you add
mutating actions to a table, use the normal Laravel CSRF mechanisms.

## Cursor input

Invalid cursors are discarded and the table restarts from the first page. Cursor
values are encoded by Laravel's cursor paginator.

Next: [Testing](testing.md).
