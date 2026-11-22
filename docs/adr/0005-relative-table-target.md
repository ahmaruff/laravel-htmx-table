# Target table fragments relative to their trigger

Table fragments are targeted with HTMX's `closest .htmx-table-wrapper` selector instead of a global id. Each trigger resolves the wrapper that contains it, so multiple tables can share a page without generated or manually managed ids.

## Consequences

The package requires an HTMX version that supports relative selectors (1.9+). Consumers that trigger a table from outside its wrapper must provide their own explicit target or table id.
