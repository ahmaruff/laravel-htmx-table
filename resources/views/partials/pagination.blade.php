@php
    $previousUrl = $rows->previousPageUrl();
    $nextUrl = $rows->nextPageUrl();

    $previousUrl = $previousUrl !== null ? $state->applyToUrl($previousUrl) : null;
    $nextUrl = $nextUrl !== null ? $state->applyToUrl($nextUrl) : null;

    $pageWindow = null;

    if (
        $paginationMode === \Ahmaruff\HtmxTable\HtmxTable::PAG_MODE_PAGINATE
        && method_exists($rows, 'lastPage')
        && $rows->lastPage() > 1
    ) {
        $pageWindow = \Illuminate\Pagination\UrlWindow::make($rows);
    }
@endphp

<div class="htmx-table-pagination">
    <div class="htmx-table-pagination-actions">
        @if ($previousUrl !== null)
            <a
                href="{{ $previousUrl }}"
                class="htmx-table-page-btn"
                rel="prev"
                hx-get="{{ $previousUrl }}"
                hx-push-url="true"
            >
                {{ trans('htmx-table::messages.pagination.previous', [], $locale) }}
            </a>
        @else
            <span class="htmx-table-page-btn is-disabled" aria-disabled="true">
                {{ trans('htmx-table::messages.pagination.previous', [], $locale) }}
            </span>
        @endif

        @if ($pageWindow !== null)
            <nav
                class="htmx-table-page-numbers"
                aria-label="{{ trans('htmx-table::messages.pagination.label', [], $locale) }}"
            >
                @php($hasRenderedSegment = false)

                @foreach (['first', 'slider', 'last'] as $segment)
                    @php($links = $pageWindow[$segment] ?? null)

                    @if (! empty($links))
                        @if ($hasRenderedSegment)
                            <span class="htmx-table-page-ellipsis" aria-hidden="true">&hellip;</span>
                        @endif

                        @foreach ($links as $page => $url)
                            @php($pageUrl = $state->applyToUrl($url))

                            @if ($page === $rows->currentPage())
                                <span
                                    class="htmx-table-page-btn is-current"
                                    aria-current="page"
                                    aria-label="{{ trans('htmx-table::messages.pagination.go_to_page', ['page' => $page], $locale) }}"
                                >
                                    {{ $page }}
                                </span>
                            @else
                                <a
                                    href="{{ $pageUrl }}"
                                    class="htmx-table-page-btn"
                                    hx-get="{{ $pageUrl }}"
                                    hx-push-url="true"
                                    aria-label="{{ trans('htmx-table::messages.pagination.go_to_page', ['page' => $page], $locale) }}"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        @php($hasRenderedSegment = true)
                    @endif
                @endforeach
            </nav>
        @endif

        @if ($nextUrl !== null)
            <a
                href="{{ $nextUrl }}"
                class="htmx-table-page-btn"
                rel="next"
                hx-get="{{ $nextUrl }}"
                hx-push-url="true"
            >
                {{ trans('htmx-table::messages.pagination.next', [], $locale) }}
            </a>
        @else
            <span class="htmx-table-page-btn is-disabled" aria-disabled="true">
                {{ trans('htmx-table::messages.pagination.next', [], $locale) }}
            </span>
        @endif
    </div>

    @if ($paginationMode === \Ahmaruff\HtmxTable\HtmxTable::PAG_MODE_PAGINATE && method_exists($rows, 'currentPage') && method_exists($rows, 'lastPage'))
        <div class="htmx-table-pagination-meta">
            {{ trans('htmx-table::messages.pagination.page_of', ['current' => $rows->currentPage(), 'last' => $rows->lastPage()], $locale) }}
        </div>
    @elseif ($paginationMode === \Ahmaruff\HtmxTable\HtmxTable::PAG_MODE_SIMPLE && method_exists($rows, 'currentPage'))
        <div class="htmx-table-pagination-meta">
            {{ trans('htmx-table::messages.pagination.page', ['page' => $rows->currentPage()], $locale) }}
        </div>
    @endif
</div>
