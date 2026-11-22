# Laravel Htmx Table

A server-driven table package for Laravel that renders a table fragment with Blade and updates it with HTMX.

## Language

**Table**:
A server-rendered view over a query source, configured by `HtmxTable`.
_Avoid_: Grid, datatable component

**Query source**:
The Eloquent or Query Builder instance that supplies a table's rows.
_Avoid_: Data source, dataset

**Column**:
A declared table column that maps a public key to a data key and controls display, sorting, searching, and cell rendering.
_Avoid_: Field, header

**Key**:
A column's stable public identifier used in sort state and URLs.
_Avoid_: Column name, sort key

**Data key**:
The column's SQL column and `data_get()` path used for filtering, ordering, and cell values. Dotted paths require a join for search and sort.
_Avoid_: Field, attribute

**Cell**:
The value or rendered output for one row and one column.
_Avoid_: Value, content

**Table state**:
The normalized request intent: search term, sort key, direction, and either page or cursor.
_Avoid_: Query state, filters

**Query-parameter alias**:
The configured request key that carries a table-state field.
_Avoid_: Query key, parameter name

**Query engine**:
The component that applies table state to a query source under the column whitelist and returns a paginator.
_Avoid_: Repository, data provider

**Pagination mode**:
One of `paginate`, `simple-paginate`, or `cursor`; it selects the Laravel paginator contract.
_Avoid_: Page type, paginator style

**Table fragment**:
The wrapper, toolbar, table, and pagination markup that HTMX replaces.
_Avoid_: Partial, widget

**Table target**:
The table fragment located relative to a trigger, normally with `closest .htmx-table-wrapper`.
_Avoid_: Table id, container

**Table identity**:
An optional DOM id for CSS, JavaScript, or triggers outside the table fragment.
_Avoid_: Table name

**Trusted HTML**:
Output intentionally exempt from Blade escaping, produced only by an explicit renderer.
_Avoid_: Raw output, safe HTML

**Tie-breaker**:
A deterministic secondary ordering column required for stable cursor pagination.
_Avoid_: Secondary sort

## Invariants

- Only columns marked sortable may order rows; an invalid sort key is ignored.
- Only columns marked searchable participate in search; conditions are grouped OR `LIKE` clauses.
- Sort direction is normalized to `asc` or `desc`.
- A pagination mode must be selected before rendering.
- Cursor pagination always applies a deterministic tie-breaker.
- Table state is parsed and rendered with the same query-parameter aliases.
- Query parameters unrelated to table state are preserved.
- Multiple tables may coexist on one page without duplicate ids.
- Cells are escaped unless they are `Htmlable`.
