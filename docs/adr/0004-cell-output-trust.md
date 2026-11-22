# Cells are escaped unless explicitly trusted

Normal cell values are escaped by Blade. A column renderer may return an `Htmlable` value, such as `HtmlString`, when it intentionally produces markup. This makes the trust boundary explicit instead of treating every database value as HTML.

## Consequences

Returning a plain string containing HTML from a renderer will render it escaped. Consumers must opt in to raw markup deliberately.
