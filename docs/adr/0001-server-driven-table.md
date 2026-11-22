# Server-driven table rendering

The package renders table fragments on the server with Blade and lets HTMX swap them, instead of shipping a client-side table runtime or a JSON API. This keeps filtering, sorting, and pagination in Laravel and avoids a second application boundary; the consumer decides whether a request receives a fragment or a full page.

## Consequences

The package exposes `HtmxTable::isFragmentRequest()` as a helper, but the host application still decides whether a request receives a fragment or a full page and must account for history-restore requests.
