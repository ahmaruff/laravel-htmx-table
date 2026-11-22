# Server-driven table rendering

The package renders table fragments on the server with Blade and lets HTMX swap them, instead of shipping a client-side table runtime or a JSON API. This keeps filtering, sorting, and pagination in Laravel and avoids a second application boundary; the consumer decides whether a request receives a fragment or a full page.

## Consequences

The package does not detect `HX-Request` itself. The host application must return the rendered fragment for HTMX requests.
