@php
    $baseUrl = url()->current();
    $search = $state->search;
    $sort = $state->sort;
    $dir = $state->dir;
@endphp

<div
    @if ($id) id="{{ $id }}" @endif
    class="htmx-table-wrapper"
    hx-target="closest .htmx-table-wrapper"
    hx-swap="outerHTML"
>
    <div class="htmx-table-toolbar">
        <input
            type="search"
            name="{{ $state->alias('search') }}"
            value="{{ $search }}"
            placeholder="Search..."
            class="htmx-table-search"
            hx-get="{{ $state->url($baseUrl, ['search' => '']) }}"
            hx-trigger="input changed delay:600ms, search"
            hx-push-url="true"
            hx-include="this"
        />
    </div>

    <table class="htmx-table">
        <thead class="htmx-table-thead">
            <tr class="htmx-table-tr">
                @foreach ($columns as $column)
                    @php
                        $key = $column->getKey();
                        $isSortable = $column->isSortable();
                        $isSorted = $isSortable && $sort === $key;
                        $nextDir = ($isSorted && $dir === 'asc') ? 'desc' : 'asc';
                        $sortUrl = $isSortable
                            ? $state->url($baseUrl, ['sort' => $key, 'dir' => $nextDir, 'page' => null, 'cursor' => null])
                            : null;
                    @endphp

                    <th
                        scope="col"
                        class="htmx-table-th {{ $isSorted ? 'is-sorted' : '' }}"
                        @if ($isSorted) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif
                    >
                        @if ($isSortable && $sortUrl !== null)
                            <a
                                href="{{ $sortUrl }}"
                                class="htmx-table-sort-link"
                                hx-get="{{ $sortUrl }}"
                                hx-push-url="true"
                            >
                                <span>{{ $column->getLabel() }}</span>
                                <span class="htmx-table-sort-indicator" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        @if ($isSorted)
                                            @if ($dir === 'asc')
                                                <path d="M8 12V4M5 7L8 4L11 7" />
                                            @else
                                                <path d="M8 4V12M5 9L8 12L11 9" />
                                            @endif
                                        @else
                                            <path d="M8 3V13M5 6L8 3L11 6M5 10L8 13L11 10" />
                                        @endif
                                    </svg>
                                </span>
                            </a>
                        @else
                            <span>{{ $column->getLabel() }}</span>
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="htmx-table-tbody">
            @forelse ($rows as $row)
                <tr class="htmx-table-tr">
                    @foreach ($columns as $column)
                        <td class="htmx-table-td">
                            {{ $column->resolveCell($row) }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr class="htmx-table-tr">
                    <td colspan="{{ count($columns) }}" class="htmx-table-td htmx-table-empty">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $previousUrl = $rows->previousPageUrl();
        $nextUrl = $rows->nextPageUrl();

        $previousUrl = $previousUrl !== null ? $state->applyToUrl($previousUrl) : null;
        $nextUrl = $nextUrl !== null ? $state->applyToUrl($nextUrl) : null;
    @endphp

    <div class="htmx-table-pagination">
        <div class="htmx-table-pagination-actions">
            @if ($previousUrl !== null)
                <a
                    href="{{ $previousUrl }}"
                    class="htmx-table-page-btn"
                    hx-get="{{ $previousUrl }}"
                    hx-push-url="true"
                >
                    Previous
                </a>
            @else
                <span class="htmx-table-page-btn is-disabled" aria-disabled="true">
                    Previous
                </span>
            @endif

            @if ($nextUrl !== null)
                <a
                    href="{{ $nextUrl }}"
                    class="htmx-table-page-btn"
                    hx-get="{{ $nextUrl }}"
                    hx-push-url="true"
                >
                    Next
                </a>
            @else
                <span class="htmx-table-page-btn is-disabled" aria-disabled="true">
                    Next
                </span>
            @endif
        </div>

        @if ($paginationMode === \Ahmaruff\HtmxTable\HtmxTable::PAG_MODE_PAGINATE && method_exists($rows, 'currentPage') && method_exists($rows, 'lastPage'))
            <div class="htmx-table-pagination-meta">
                Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}
            </div>
        @elseif ($paginationMode === \Ahmaruff\HtmxTable\HtmxTable::PAG_MODE_SIMPLE && method_exists($rows, 'currentPage'))
            <div class="htmx-table-pagination-meta">
                Page {{ $rows->currentPage() }}
            </div>
        @endif
    </div>
</div>
