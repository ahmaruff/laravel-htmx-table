<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

final class CellRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('items')->insert([
            ['id' => 1, 'name' => '<script>alert(1)</script>', 'status' => 'active', 'secret_code' => 'S-1'],
        ]);
    }

    public function test_plain_cell_values_are_escaped(): void
    {
        $html = $this->renderTable([
            Column::make('name'),
        ]);

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_htmlable_renderer_output_is_not_escaped(): void
    {
        $html = $this->renderTable([
            Column::make('name')->render(
                static fn (mixed $row, mixed $value): HtmlString => new HtmlString('<strong>'.e($value).'</strong>'),
            ),
        ]);

        $this->assertStringContainsString('<strong>&lt;script&gt;alert(1)&lt;/script&gt;</strong>', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_plain_string_renderer_output_is_escaped(): void
    {
        $html = $this->renderTable([
            Column::make('name')->render(
                static fn (mixed $row, mixed $value): string => '<em>'.$value.'</em>',
            ),
        ]);

        $this->assertStringContainsString('&lt;em&gt;&lt;script&gt;alert(1)&lt;/script&gt;&lt;/em&gt;', $html);
        $this->assertStringNotContainsString('<em>', $html);
    }

    /**
     * @param  list<Column>  $columns
     */
    private function renderTable(array $columns): string
    {
        return HtmxTable::make(DB::table('items'))
            ->columns($columns)
            ->paginate(10)
            ->render(Request::create('/items'))
            ->render();
    }
}
