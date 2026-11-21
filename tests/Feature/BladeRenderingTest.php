<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Ahmaruff\HtmxTable\Tests\TestCase;
use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;

final class BladeRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('items')->insert([
            ['id' => 1, 'name' => 'Alpha', 'status' => 'active', 'secret_code' => 'S-1'],
            ['id' => 2, 'name' => 'Beta', 'status' => 'active', 'secret_code' => 'S-2'],
            ['id' => 3, 'name' => 'Gamma', 'status' => 'inactive', 'secret_code' => 'S-3'],
        ]);
    }

    public function test_renders_semantic_classes_search_input_and_sort_htmx_attributes(): void
    {
        $request = Request::create('/items', 'GET', [
            'search' => 'a',
            'sort' => 'name',
            'dir' => 'asc',
        ]);

        $html = HtmxTable::make(DB::table('items'))
            ->columns([
                Column::make('name')->searchable()->sortable(),
                Column::make('status')->sortable(),
            ])
            ->paginate(2)
            ->render($request)
            ->render();

        $xpath = $this->makeXpath($html);

        // Wrapper, table, header, and search input use single semantic htmx-table-* classes
        $this->assertCount(1, $xpath->query("//div[@id='htmx-table' and contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]"));
        $this->assertCount(1, $xpath->query("//table[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table ')]"));

        $searchInputs = $xpath->query("//input[@name='search' and contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-search ')]");
        $this->assertCount(1, $searchInputs);

        $searchInput = $searchInputs->item(0);
        $this->assertSame('a', $this->getAttributeValue($searchInput, 'value'));
        $this->assertSame('#htmx-table', $this->getAttributeValue($searchInput, 'hx-target'));
        $this->assertSame('outerHTML', $this->getAttributeValue($searchInput, 'hx-swap'));

        // Active 'name' sort header toggles to 'desc', preserves 'search=a', and resets 'page'/'cursor'
        $sortLinks = $xpath->query("//th[contains(concat(' ', normalize-space(@class), ' '), ' is-sorted ')]//a[@hx-get]");
        $this->assertCount(1, $sortLinks);

        $sortLink = $sortLinks->item(0);
        $hxGet = $this->getAttributeValue($sortLink, 'hx-get');
        $query = $this->parseQuery($hxGet);

        $this->assertSame('name', $query['sort'] ?? null);
        $this->assertSame('desc', $query['dir'] ?? null);
        $this->assertSame('a', $query['search'] ?? null);
        $this->assertArrayNotHasKey('page', $query);
        $this->assertArrayNotHasKey('cursor', $query);
    }

    public function test_pagination_links_preserve_active_search_and_sort_state(): void
    {
        $request = Request::create('/items', 'GET', [
            'search' => 'a',
            'sort' => 'name',
            'dir' => 'asc',
        ]);

        $html = HtmxTable::make(DB::table('items'))
            ->columns([
                Column::make('name')->searchable()->sortable(),
                Column::make('status'),
            ])
            ->paginate(1) // 2 matching rows ('Alpha', 'Beta') -> page 1 has a Next link
            ->render($request)
            ->render();

        $xpath = $this->makeXpath($html);

        $nextLinks = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-btn ') and normalize-space(.)='Next']");
        $this->assertCount(1, $nextLinks);

        $nextLink = $nextLinks->item(0);
        $nextHxGet = $this->getAttributeValue($nextLink, 'hx-get');
        $query = $this->parseQuery($nextHxGet);

        $this->assertSame('2', $query['page'] ?? null);
        $this->assertSame('a', $query['search'] ?? null);
        $this->assertSame('name', $query['sort'] ?? null);
        $this->assertSame('asc', $query['dir'] ?? null);
    }

    private function makeXpath(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($doc);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseQuery(string $url): array
    {
        $queryString = parse_url($url, PHP_URL_QUERY);
        if (! is_string($queryString) || $queryString === '') {
            return [];
        }

        $params = [];
        parse_str($queryString, $params);

        return $params;
    }

    private function getAttributeValue(\DOMNode|null $node, string $attribute): ?string
    {
        if (! $node instanceof \DOMElement) {
            return null;
        }

        $value = $node->getAttribute($attribute);

        return $value !== '' ? $value : null;
    }
}