<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\TestCase;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class UrlStateRenderingTest extends TestCase
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

    public function test_rendered_urls_use_configured_aliases_and_preserve_unrelated_params(): void
    {
        config()->set('htmx-table.query_params', [
            'search' => 'q',
            'sort' => 'order_by',
            'dir' => 'order_dir',
            'page' => 'p',
            'cursor' => 'c',
        ]);

        $request = Request::create('/items', 'GET', [
            'q' => 'a',
            'order_by' => 'name',
            'order_dir' => 'asc',
            'filter' => 'active',
        ]);

        $html = HtmxTable::make(DB::table('items'))
            ->columns([
                Column::make('name')->searchable()->sortable(),
                Column::make('status')->sortable(),
            ])
            ->paginate(1)
            ->render($request)
            ->render();

        $xpath = $this->makeXpath($html);

        $searchInput = $xpath->query("//input[@name='q']")->item(0);
        $this->assertNotNull($searchInput);
        $this->assertSame('a', $searchInput->getAttribute('value'));

        $searchUrl = $searchInput->getAttribute('hx-get');
        $searchQuery = $this->parseQuery($searchUrl);

        $this->assertSame('active', $searchQuery['filter'] ?? null);
        $this->assertSame('name', $searchQuery['order_by'] ?? null);
        $this->assertSame('asc', $searchQuery['order_dir'] ?? null);
        $this->assertArrayNotHasKey('q', $searchQuery);

        $sortLink = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-sort-link ')]")->item(0);
        $this->assertNotNull($sortLink);

        $sortQuery = $this->parseQuery($sortLink->getAttribute('hx-get'));

        $this->assertSame('a', $sortQuery['q'] ?? null);
        $this->assertSame('name', $sortQuery['order_by'] ?? null);
        $this->assertSame('desc', $sortQuery['order_dir'] ?? null);
        $this->assertSame('active', $sortQuery['filter'] ?? null);
        $this->assertArrayNotHasKey('p', $sortQuery);

        $nextLink = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-page-btn ') and normalize-space(.)='Next']")->item(0);
        $this->assertNotNull($nextLink);

        $nextQuery = $this->parseQuery($nextLink->getAttribute('hx-get'));

        $this->assertSame('2', $nextQuery['p'] ?? null);
        $this->assertSame('a', $nextQuery['q'] ?? null);
        $this->assertSame('name', $nextQuery['order_by'] ?? null);
        $this->assertSame('asc', $nextQuery['order_dir'] ?? null);
        $this->assertSame('active', $nextQuery['filter'] ?? null);
    }

    private function makeXpath(string $html): DOMXPath
    {
        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
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
}
