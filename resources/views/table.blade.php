@php
    $baseUrl = url()->current();
    $sort = $state->sort;
    $dir = $state->dir;
    $wrapperClasses = trim('htmx-table-wrapper '.implode(' ', $classes));
@endphp

<div
    @if ($id) id="{{ $id }}" @endif
    class="{{ $wrapperClasses }}"
    hx-target="closest .htmx-table-wrapper"
    hx-swap="outerHTML"
    @if ($focusSearch) hx-on::load="var input = this.querySelector('.htmx-table-search'); if (input) { input.focus(); input.setSelectionRange(input.value.length, input.value.length); }" @endif
>
    @include('htmx-table::partials.toolbar')

    <div class="htmx-table-scroll">
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
                            {{ trans('htmx-table::messages.empty', [], $locale) }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('htmx-table::partials.pagination')
</div>
