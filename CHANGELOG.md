# Release Notes

## [Unreleased](https://github.com/ahmaruff/laravel-htmx-table/compare/v0.1.0...1.x)

### Added

- Domain glossary and architecture decision records.
- Documentation for installation, quickstart, columns, search, sorting, pagination, customization, configuration, security, testing, and troubleshooting.
- Optional table ids for CSS, JavaScript, and external HTMX triggers.
- Multi-table support through relative `closest .htmx-table-wrapper` targeting.
- `TableState` for consistent request parsing and URL generation.

### Changed

- Cell values are escaped by default; renderers must return `Htmlable` for raw HTML.
- Query-parameter aliases are used in generated URLs and the search input.
- Query parameters unrelated to table state are preserved.
- The Blade component now accepts a `HtmxTable` instance.
- The stylesheet publish tag is now `htmx-table-assets`.
- Publish tags in the README now match the service provider.

### Fixed

- The workbench demo retains its pagination mode after search, sort, and pagination actions.
- Invalid sort directions and pages are normalized consistently.

## [v0.1.0](https://github.com/ahmaruff/laravel-htmx-table/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
