# Whitelist search and sort state

Search and sort accept only columns explicitly declared as searchable or sortable. This prevents request input from selecting arbitrary database columns and keeps the table definition as the single source of truth.

## Consequences

An unknown sort key or a non-sortable column is ignored rather than rejected. Invalid directions fall back to `asc`.
