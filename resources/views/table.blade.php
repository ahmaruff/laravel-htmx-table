@php
    $baseUrl = url()->current();

    $search = $state['search'] ?? '';
    $sort = $state['sort'] ?? '';
    $dir = $state['dir'] ?? '';

    $buildUrl = static function(array $overrides = []) use ($baseUrl, $search, $sort, $dir): string {
        $params = array_filter([
            'search' => $search !== '' ? $search : null,
            'sort' => $sort !== '' ? $sort : null,
            'dir' => ($sort !== '' || isset($overrides['sort'])) ? $dir : null,
        ], static fn ($value) => $value !== null && $value !== '');

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($params[$key]);
            } else {
                $params[$key] = $value;
            }
        }

        $query = http_build_query($params);
        return $query !== '' ? "{$baseUrl}?{$query}" : $baseUrl;
    };

    $appendStateToPaginatorUrl = static function (?string $paginatorUrl) use ($search, $sort, $dir): ?string {
        if ($paginatorUrl === null) {
            return null;
        }

        $parts = parse_url($paginatorUrl);
        $existingQuery = [];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $existingQuery);
        }

        $merged = array_filter([
            ...$existingQuery,
            'search' => $search !== '' ? $search : null,
            'sort' => $sort !== '' ? $sort : null,
            'dir' => $sort !== '' ? $dir : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $scheme = isset($parts['scheme']) ? "{$parts['scheme']}://" : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ":{$parts['port']}" : '';
        $path = $parts['path'] ?? url()->current();
        $queryString = http_build_query($merged);

        return $queryString !== ''
            ? "{$scheme}{$host}{$port}{$path}?{$queryString}"
            : "{$scheme}{$host}{$port}{$path}";
    };
@endphp

<div id="htmx-table" class="htmx-table-wrapper">
    <div class="htmx-table-toolbar">
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="Search..."
            class="htmx-table-search"
            hx-get="{{ $buildUrl(['page' => null, 'cursor' => null]) }}"
            hx-trigger="input changed delay:300ms, search"
            hx-target="#htmx-table"
            hx-swap="outerHTML"
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
                            ? $buildUrl(['sort' => $key, 'dir' => $nextDir, 'page' => null, 'cursor' => null])
                            : null;
                    @endphp

                    <th scope="col" class="htmx-table-th {{ $isSorted ? 'is-sorted' : '' }}">
                        @if ($isSortable && $sortUrl !== null)
                            <a
                                href="{{ $sortUrl }}"
                                class="htmx-table-sort-link"
                                hx-get="{{ $sortUrl }}"
                                hx-target="#htmx-table"
                                hx-swap="outerHTML"
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
                            {!! $column->resolveCell($row) !!}
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
        $prevUrl = $appendStateToPaginatorUrl($rows->previousPageUrl());
        $nextUrl = $appendStateToPaginatorUrl($rows->nextPageUrl());
    @endphp

    <div class="htmx-table-pagination">
        <div class="htmx-table-pagination-actions">
            @if ($prevUrl !== null)
                <a
                    href="{{ $prevUrl }}"
                    class="htmx-table-page-btn"
                    hx-get="{{ $prevUrl }}"
                    hx-target="#htmx-table"
                    hx-swap="outerHTML"
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
                    hx-target="#htmx-table"
                    hx-swap="outerHTML"
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