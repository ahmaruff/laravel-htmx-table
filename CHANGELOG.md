# Release Notes

## [Unreleased](https://github.com/ahmaruff/laravel-htmx-table/compare/v0.1.0...1.x)

### Added

- PHP 8.2–8.5 and Laravel 10–13 compatibility, including best-effort support for end-of-life Laravel 10 and 11.
- Domain glossary and architecture decision records.
- Documentation for installation, quickstart, columns, search, sorting, pagination, customization, custom views, localization, configuration, security, testing, and troubleshooting.
- Optional table ids for CSS, JavaScript, and external HTMX triggers.
- Multi-table support through relative `closest .htmx-table-wrapper` targeting.
- `TableState` for consistent request parsing and URL generation.
- Numbered page links for length-aware pagination using Laravel's `UrlWindow`.
- Opt-in per-page selector through `perPageOptions()`.
- Per-table styling with `classes()` and full template overrides with `view()`.
- English and Indonesian translations, per-table locales, and `searchPlaceholder()`.
- `HtmxTable::isFragmentRequest()` for history-safe fragment routing.
- Search refocus with the caret at the end after HTMX swaps, plus a horizontal scroll container for wide tables.

### Changed

- Cell values are escaped by default; renderers must return `Htmlable` for raw HTML.
- Query-parameter aliases are used in generated URLs and the search input.
- Query parameters unrelated to table state are preserved.
- The Blade component now accepts a `HtmxTable` instance.
- The stylesheet publish tag is now `htmx-table-assets`.
- Publish tags in the README now match the service provider.
- Pagination state now includes `per_page` when a per-page selector is enabled.
- The sort indicator is dimmed until a column is actively sorted.

### Fixed

- The workbench demo retains its pagination mode after search, sort, and pagination actions.
- Invalid sort directions and pages are normalized consistently.
- Browser back/forward no longer renders a table fragment as the whole page when using the documented route guard.

## [v0.1.0](https://github.com/ahmaruff/laravel-htmx-table/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
