<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\Concerns\InteractsWithTableHtml;
use Ahmaruff\HtmxTable\Tests\TestCase;
use DOMElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

final class CustomizationRenderingTest extends TestCase
{
    use InteractsWithTableHtml;

    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/../Fixtures/views');

        DB::table('items')->insert([
            ['id' => 1, 'name' => 'Alpha', 'status' => 'active', 'secret_code' => 'S-1'],
            ['id' => 2, 'name' => 'Beta', 'status' => 'active', 'secret_code' => 'S-2'],
        ]);
    }

    public function test_custom_classes_are_added_to_the_wrapper(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->classes('table-compact', 'theme-dark')
            ->classes('table-compact')
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $wrapper = $this->makeXpath($html)->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]")->item(0);

        $this->assertInstanceOf(DOMElement::class, $wrapper);
        $this->assertStringContainsString('table-compact', $wrapper->getAttribute('class'));
        $this->assertStringContainsString('theme-dark', $wrapper->getAttribute('class'));
    }

    public function test_default_view_renders_scroll_container_and_search_request_header(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $xpath = $this->makeXpath($html);

        $searchInput = $xpath->query("//input[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-search ')]")->item(0);
        $this->assertInstanceOf(DOMElement::class, $searchInput);
        $this->assertStringContainsString(HtmxTable::SEARCH_HEADER, $searchInput->getAttribute('hx-headers'));
        $this->assertFalse($searchInput->hasAttribute('autofocus'));

        $wrapper = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]")->item(0);
        $this->assertInstanceOf(DOMElement::class, $wrapper);
        $this->assertFalse($wrapper->hasAttribute('hx-on::load'));

        $this->assertCount(1, $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-scroll ')]/table"));
    }

    public function test_search_input_is_autofocused_only_for_search_requests(): void
    {
        $searchRequest = Request::create('/items', 'GET', [], [], [], ['HTTP_X_HTMX_TABLE_SEARCH' => 'true']);

        $searchHtml = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->paginate(1)
            ->render($searchRequest)
            ->render();

        $searchInput = $this->makeXpath($searchHtml)->query("//input[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-search ')]")->item(0);

        $this->assertInstanceOf(DOMElement::class, $searchInput);
        $this->assertTrue($searchInput->hasAttribute('autofocus'));

        $searchWrapper = $this->makeXpath($searchHtml)->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]")->item(0);
        $this->assertInstanceOf(DOMElement::class, $searchWrapper);
        $this->assertStringContainsString('setSelectionRange', $searchWrapper->getAttribute('hx-on::load'));

        $normalHtml = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $normalInput = $this->makeXpath($normalHtml)->query("//input[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-search ')]")->item(0);

        $this->assertInstanceOf(DOMElement::class, $normalInput);
        $this->assertFalse($normalInput->hasAttribute('autofocus'));

        $normalWrapper = $this->makeXpath($normalHtml)->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]")->item(0);
        $this->assertInstanceOf(DOMElement::class, $normalWrapper);
        $this->assertFalse($normalWrapper->hasAttribute('hx-on::load'));
    }

    public function test_custom_view_receives_the_table_data_contract(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->view('custom-table')
            ->locale('id')
            ->searchPlaceholder('Cari user...')
            ->perPageOptions([1, 2])
            ->paginate(1)
            ->render(Request::create('/items', 'GET', ['per_page' => 2]))
            ->render();

        $xpath = $this->makeXpath($html);
        $customTable = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' custom-table ')]")->item(0);

        $this->assertInstanceOf(DOMElement::class, $customTable);
        $this->assertSame('id', $customTable->getAttribute('data-locale'));
        $this->assertSame('Cari user...', $customTable->getAttribute('data-placeholder'));
        $this->assertSame('2', $customTable->getAttribute('data-per-page'));
        $this->assertSame('2', $customTable->getAttribute('data-rows'));
        $this->assertSame('Berikutnya', trim($customTable->textContent));
    }

    public function test_view_names_must_not_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        HtmxTable::make(DB::table('items'))->view(' ');
    }

    public function test_default_view_name_is_exposed(): void
    {
        $table = HtmxTable::make(DB::table('items'));

        $this->assertSame('htmx-table::table', $table->getView());
    }
}
