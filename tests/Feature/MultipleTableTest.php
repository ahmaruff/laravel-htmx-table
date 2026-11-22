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
use InvalidArgumentException;

final class MultipleTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('items')->insert([
            ['id' => 1, 'name' => 'Alpha', 'status' => 'active', 'secret_code' => 'S-1'],
            ['id' => 2, 'name' => 'Beta', 'status' => 'inactive', 'secret_code' => 'S-2'],
        ]);
    }

    public function test_multiple_tables_render_without_ids_and_target_their_own_wrapper(): void
    {
        $request = Request::create('/items', 'GET', ['filter' => 'active']);

        $first = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()->sortable()])
            ->paginate(1)
            ->render($request)
            ->render();

        $second = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('status')->searchable()->sortable()])
            ->paginate(1)
            ->render($request)
            ->render();

        $xpath = $this->makeXpath($first.$second);

        $wrappers = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]");
        $this->assertCount(2, $wrappers);

        foreach ($wrappers as $wrapper) {
            $this->assertSame('', $wrapper->getAttribute('id'));
            $this->assertSame('closest .htmx-table-wrapper', $wrapper->getAttribute('hx-target'));
            $this->assertSame('outerHTML', $wrapper->getAttribute('hx-swap'));
        }

        $sortLinks = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-sort-link ')]");
        $this->assertCount(2, $sortLinks);

        foreach ($sortLinks as $sortLink) {
            $query = $this->parseQuery($sortLink->getAttribute('hx-get'));

            $this->assertSame('active', $query['filter'] ?? null);
        }
    }

    public function test_an_explicit_id_is_rendered_for_external_triggers(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->id('users-table')
            ->columns([Column::make('name')])
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $xpath = $this->makeXpath($html);

        $this->assertCount(
            1,
            $xpath->query("//div[@id='users-table' and contains(concat(' ', normalize-space(@class), ' '), ' htmx-table-wrapper ')]"),
        );
    }

    public function test_an_empty_table_id_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        HtmxTable::make(DB::table('items'))->id(' ');
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
