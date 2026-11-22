<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\Concerns\InteractsWithTableHtml;
use Ahmaruff\HtmxTable\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PaginationRenderingTest extends TestCase
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

    public function test_length_aware_pagination_renders_page_numbers_and_preserves_state(): void
    {
        $request = Request::create('/items', 'GET', [
            'search' => 'Item',
            'sort' => 'name',
            'dir' => 'asc',
            'page' => 2,
        ]);

        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()->sortable()])
            ->paginate(2)
            ->render($request)
            ->render();

        $xpath = $this->makeXpath($html);

        $this->assertCount(1, $xpath->query("//nav[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-numbers ')]"));

        $currentPage = $xpath->query("//*[@aria-current='page']")->item(0);
        $this->assertNotNull($currentPage);
        $this->assertSame('2', trim((string) $currentPage->textContent));

        $pageThree = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-btn ') and normalize-space(.)='3']")->item(0);
        $this->assertNotNull($pageThree);

        $query = $this->parseQuery($pageThree->getAttribute('hx-get'));

        $this->assertSame('3', $query['page'] ?? null);
        $this->assertSame('Item', $query['search'] ?? null);
        $this->assertSame('name', $query['sort'] ?? null);
        $this->assertSame('asc', $query['dir'] ?? null);
    }

    public function test_large_pagination_window_renders_ellipsis_and_first_last_pages(): void
    {
        $request = Request::create('/items', 'GET', ['page' => 7]);

        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->sortable()])
            ->paginate(2)
            ->render($request)
            ->render();

        $xpath = $this->makeXpath($html);

        $this->assertCount(2, $xpath->query("//span[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-ellipsis ')]"));
        $this->assertCount(1, $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-btn ') and normalize-space(.)='1']"));
        $this->assertCount(1, $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-btn ') and normalize-space(.)='15']"));

        $currentPage = $xpath->query("//*[@aria-current='page']")->item(0);
        $this->assertNotNull($currentPage);
        $this->assertSame('7', trim((string) $currentPage->textContent));
    }

    public function test_simple_pagination_does_not_render_page_numbers(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->simplePaginate(2)
            ->render(Request::create('/items'))
            ->render();

        $xpath = $this->makeXpath($html);

        $this->assertCount(0, $xpath->query("//nav[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-numbers ')]"));
        $this->assertStringContainsString('Page 1', $html);
    }
}
