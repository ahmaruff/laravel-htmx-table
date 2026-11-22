<div
    class="custom-table"
    data-locale="{{ $locale }}"
    data-placeholder="{{ $searchPlaceholder }}"
    data-per-page="{{ $perPage }}"
    data-rows="{{ count($rows->items()) }}"
>
    {{ trans('htmx-table::messages.pagination.next', [], $locale) }}
</div>
