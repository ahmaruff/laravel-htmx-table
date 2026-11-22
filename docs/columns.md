# Columns

```php
use Ahmaruff\HtmxTable\Column;

Column::make('name')
    ->label('Full name')
    ->dataKey('users.name')
    ->searchable()
    ->sortable();
```

## Key and data key

`key` is the stable public identifier used in sort state and URLs. It defaults
the data key and the label.

`dataKey` is the SQL column used for search and ordering, and the `data_get()`
path used to read a cell value. When it is omitted, the key is used.

```php
Column::make('author')->dataKey('users.name');
```

Dotted data keys require a join for search and sort. They do not add automatic
relation query support. For example, join `users` and use `users.name` when the
table query does not already contain that column.

## Labels

```php
Column::make('created_at');              // Created At
Column::make('created_at')->label('Joined');
```

Without an explicit label, the key is converted to a headline-style label.

## Searchable and sortable

```php
Column::make('name')->searchable()->sortable();
Column::make('email')->searchable();
Column::make('secret_code');             // not searchable or sortable
```

Search only includes searchable columns. Sort only accepts sortable columns;
unknown keys and non-sortable columns are ignored.

## Cell rendering

Values are escaped by default:

```php
Column::make('name');
```

Use an explicit renderer for markup. Return an `Htmlable` value such as
`HtmlString` to opt out of escaping:

```php
use Illuminate\Support\HtmlString;

Column::make('status')->render(
    fn (mixed $row, mixed $value): HtmlString => new HtmlString(
        '<span class="badge">'.e($value).'</span>'
    ),
);
```

The renderer receives the row and the resolved value:

```php
Column::make('name')->render(
    fn (mixed $row, mixed $value): string => strtoupper((string) $value),
);
```

A plain string returned by a renderer is still escaped. Only `Htmlable` output
is treated as trusted HTML. See [Security](security.md).

Next: [Searching and sorting](searching-sorting.md).
