<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\Exceptions\InvalidColumnException;
use Ahmaruff\HtmxTable\Exceptions\MissingPaginationModeException;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class QueryEngineAndTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('items')->insert([
            ['id' => 1, 'name' => 'Alpha', 'status' => 'active', 'secret_code' => 'FINDME-1'],
            ['id' => 2, 'name' => 'Beta', 'status' => 'active', 'secret_code' => 'FINDME-2'],
            ['id' => 3, 'name' => 'Gamma', 'status' => 'active', 'secret_code' => 'OTHER-3'],
            ['id' => 4, 'name' => 'Delta', 'status' => 'inactive', 'secret_code' => 'FINDME-4'],
        ]);
    }

    public function test_search_filters_only_searchable_columns_with_grouped_conditions(): void
    {
        $columns = [
            Column::make('name')->searchable()->sortable(),
            Column::make('status')->sortable(),
            Column::make('secret_code'), // intentionally not searchable
        ];

        // test 1
        $request = Request::create('/items', 'GET', ['search' => 'FINDME']);

        $paginator = HtmxTable::make(DB::table('items'))
            ->columns($columns)
            ->paginate(10)
            ->render($request)
            ->getData()['rows'];

        $ids = collect($paginator->items())->pluck('id')->all();

        // secret_code matches FINDME, but is not marked searchable so no rows match
        $this->assertSame([], $ids);

        $nameSearchRequest = Request::create('/items', 'GET', ['search' => 'alp']);
        $matchedNames = collect(
            HtmxTable::make(DB::table('items'))
                ->columns($columns)
                ->paginate(10)
                ->render($nameSearchRequest)
                ->getData()['rows']
                ->items(),
        )->pluck('name')->all();

        // name match alp
        $this->assertSame(['Alpha'], $matchedNames);
    }

    public function test_sort_whitelists_sortable_columns_and_guards_direction(): void
    {
        $columns = [
            Column::make('name')->sortable(),
            Column::make('status'), // not sortable
        ];

        // Non-sortable column should be ignored rather than sorting by status
        $ignoredSortRequest = Request::create('/items', 'GET', [
            'sort' => 'status',
            'dir' => 'desc',
        ]);

        $defaultOrderIds = collect(
            HtmxTable::make(DB::table('items'))
                ->columns($columns)
                ->paginate(10)
                ->render($ignoredSortRequest)
                ->getData()['rows']
                ->items(),
        )->pluck('id')->all();

        $this->assertSame([1, 2, 3, 4], $defaultOrderIds);

        // Invalid direction should fall back to asc on a sortable column
        $invalidDirRequest = Request::create('/items', 'GET', [
            'sort' => 'name',
            'dir' => 'sideways',
        ]);

        $sortedNames = collect(
            HtmxTable::make(DB::table('items'))
                ->columns($columns)
                ->paginate(10)
                ->render($invalidDirRequest)
                ->getData()['rows']
                ->items(),
        )->pluck('name')->all();

        $this->assertSame(['Alpha', 'Beta', 'Delta', 'Gamma'], $sortedNames);
    }

    public function test_cursor_pagination_uses_deterministic_tie_breaker_on_non_unique_column(): void
    {
        $columns = [
            Column::make('status')->sortable(),
            Column::make('name')->sortable(),
        ];

        // Rows 1, 2, 3 all share status = 'active'
        $page1Request = Request::create('/items', 'GET', [
            'sort' => 'status',
            'dir' => 'asc',
        ]);

        $page1 = HtmxTable::make(DB::table('items'))
            ->columns($columns)
            ->cursorPaginate(2)
            ->render($page1Request)
            ->getData()['rows'];

        $this->assertCount(2, $page1->items());
        $this->assertNotNull($page1->nextCursor());

        $page2Request = Request::create('/items', 'GET', [
            'sort' => 'status',
            'dir' => 'asc',
            'cursor' => $page1->nextCursor()->encode(),
        ]);

        $page2 = HtmxTable::make(DB::table('items'))
            ->columns($columns)
            ->cursorPaginate(2)
            ->render($page2Request)
            ->getData()['rows'];

        $page1Ids = collect($page1->items())->pluck('id')->all();
        $page2Ids = collect($page2->items())->pluck('id')->all();

        // No overlapping rows across cursor boundaries despite identical status values
        $this->assertSame([], array_intersect($page1Ids, $page2Ids));
    }

    public function test_custom_query_params_and_per_page_config_are_respected(): void
    {
        config()->set('htmx-table.per_page', 2);
        config()->set('htmx-table.query_params', [
            'search' => 'q',
            'sort' => 'order_by',
            'dir' => 'order_dir',
            'page' => 'p',
            'cursor' => 'c',
        ]);

        $columns = [
            Column::make('name')->searchable()->sortable(),
        ];

        $request = Request::create('/items', 'GET', [
            'q' => 'a',
            'order_by' => 'name',
            'order_dir' => 'desc',
        ]);

        $table = HtmxTable::make(DB::table('items'))
            ->columns($columns)
            ->paginate(); // uses configured per_page = 2

        $viewData = $table->render($request)->getData();

        $this->assertSame(2, $table->getPerPage());
        $this->assertSame('a', $viewData['state']->search);
        $this->assertSame('name', $viewData['state']->sort);
        $this->assertSame('desc', $viewData['state']->dir);
    }

    public function test_columns_must_be_column_instances(): void
    {
        $this->expectException(InvalidColumnException::class);

        HtmxTable::make(DB::table('items'))->columns(['name']);
    }

    public function test_render_throws_when_pagination_mode_is_missing(): void
    {
        $this->expectException(MissingPaginationModeException::class);

        HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->render(Request::create('/items'));
    }
}
