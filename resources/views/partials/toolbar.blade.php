<div class="htmx-table-toolbar">
    <input
        type="search"
        name="{{ $state->alias('search') }}"
        value="{{ $state->search }}"
        placeholder="{{ $searchPlaceholder }}"
        aria-label="{{ trans('htmx-table::messages.search.label', [], $locale) }}"
        class="htmx-table-search"
        @if ($focusSearch) autofocus @endif
        hx-headers='{"{{\Ahmaruff\HtmxTable\HtmxTable::SEARCH_HEADER }}":"true"}'
        hx-get="{{ $state->url($baseUrl, ['search' => '']) }}"
        hx-trigger="input changed delay:600ms, search"
        hx-push-url="true"
        hx-include="this"
    />

    @if ($perPageOptions !== [])
        <label class="htmx-table-per-page">
            <span>{{ trans('htmx-table::messages.pagination.per_page', [], $locale) }}</span>
            <select
                name="{{ $state->alias('per_page') }}"
                class="htmx-table-select"
                hx-get="{{ $state->url($baseUrl, ['page' => null, 'cursor' => null, 'per_page' => null]) }}"
                hx-trigger="change"
                hx-push-url="true"
                hx-include="this"
            >
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected($option === $perPage)>{{ $option }}</option>
                @endforeach
            </select>
        </label>
    @endif
</div>
