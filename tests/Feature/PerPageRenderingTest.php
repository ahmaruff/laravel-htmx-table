<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\Concerns\InteractsWithTableHtml;
use Ahmaruff\HtmxTable\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PerPageRenderingTest extends TestCase
{
    use InteractsWithTableHtml;

    protected function setUp(): void
    {
        parent::setUp();

        $items = [];

        for ($i = 1; $i <= 30; $i++) {
            $items[] = [
                'id' => $i,
                'name' => sprintf('Item %02d', $i),
                'status' => 'active',
                'secret_code' => 'S-'.$i,
            ];
        }

        DB::table('items')->insert($items);
    }

    public function test_per_page_selector_uses_a_validated_request_value_and_preserves_it_in_page_urls(): void
    {
        $request = Request::create('/items', 'GET', [
            'search' => 'Item',
            'page' => 2,
            'per_page' => 5,
        ]);

        $view = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()->sortable()])
            ->perPageOptions([5, 10])
            ->paginate(10)
            ->render($request);

        $html = $view->render();

        $this->assertSame(5, $view->getData()['perPage']);
        $this->assertSame(5, $view->getData()['state']->perPage);

        $xpath = $this->makeXpath($html);

        $select = $xpath->query("//select[@name='per_page']")->item(0);
        $this->assertNotNull($select);

        $selected = $xpath->query("//select[@name='per_page']/option[@selected]")->item(0);
        $this->assertNotNull($selected);
        $this->assertSame('5', $selected->getAttribute('value'));

        $this->assertCount(5, $xpath->query("//tbody/tr[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-tr ')]"));

        $pageThree = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-btn ') and normalize-space(.)='3']")->item(0);
        $this->assertNotNull($pageThree);

        $query = $this->parseQuery($pageThree->getAttribute('hx-get'));

        $this->assertSame('5', $query['per_page'] ?? null);
        $this->assertSame('Item', $query['search'] ?? null);
        $this->assertSame('3', $query['page'] ?? null);
    }

    public function test_changing_per_page_resets_page_state_in_the_selector_url(): void
    {
        $request = Request::create('/items', 'GET', [
            'search' => 'Item',
            'sort' => 'name',
            'dir' => 'desc',
            'page' => 3,
            'per_page' => 5,
        ]);

        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()->sortable()])
            ->perPageOptions([5, 10])
            ->paginate(10)
            ->render($request)
            ->render();

        $select = $this->makeXpath($html)->query("//select[@name='per_page']")->item(0);
        $this->assertNotNull($select);

        $query = $this->parseQuery($select->getAttribute('hx-get'));

        $this->assertArrayNotHasKey('page', $query);
        $this->assertArrayNotHasKey('cursor', $query);
        $this->assertArrayNotHasKey('per_page', $query);
        $this->assertSame('Item', $query['search'] ?? null);
        $this->assertSame('name', $query['sort'] ?? null);
        $this->assertSame('desc', $query['dir'] ?? null);
    }

    public function test_invalid_per_page_falls_back_to_the_table_default(): void
    {
        $request = Request::create('/items', 'GET', ['per_page' => 999]);

        $view = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->perPageOptions([5, 10])
            ->paginate(10)
            ->render($request);

        $this->assertSame(10, $view->getData()['perPage']);
        $this->assertSame(10, $view->getData()['state']->perPage);
    }

    public function test_per_page_option_is_prepended_when_the_default_is_missing(): void
    {
        $table = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->perPageOptions([10, 25])
            ->paginate(15);

        $this->assertSame([15, 10, 25], $table->getPerPageOptions());
    }

    public function test_no_selector_is_rendered_without_per_page_options(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->paginate(10)
            ->render(Request::create('/items', 'GET', ['per_page' => 5]))
            ->render();

        $this->assertCount(0, $this->makeXpath($html)->query("//select[@name='per_page']"));
    }

    public function test_per_page_options_must_be_positive_integers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        HtmxTable::make(DB::table('items'))->perPageOptions([0]);
    }
}
