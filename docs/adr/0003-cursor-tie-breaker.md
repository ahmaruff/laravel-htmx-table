# Cursor pagination requires a tie-breaker

Cursor pagination orders by the requested sort column and then by the model key (or `id` for query-builder sources) when the sort column is not already the key. Without a deterministic secondary order, rows with equal sort values can overlap or disappear across pages.

## Consequences

Cursor mode always applies a deterministic order, even when no sort is requested. An invalid cursor restarts from the first page.
