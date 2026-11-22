<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\Concerns\InteractsWithTableHtml;
use Ahmaruff\HtmxTable\Tests\TestCase;
use DOMElement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LocalizationRenderingTest extends TestCase
{
    use InteractsWithTableHtml;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        DB::table('items')->insert([
            ['id' => 1, 'name' => 'Alpha', 'status' => 'active', 'secret_code' => 'S-1'],
            ['id' => 2, 'name' => 'Beta', 'status' => 'active', 'secret_code' => 'S-2'],
        ]);
    }

    public function test_english_is_the_default_language(): void
    {
        $html = $this->renderTable()->render();

        $this->assertStringContainsString('Search...', $html);
        $this->assertStringContainsString('Previous', $html);
        $this->assertStringContainsString('Next', $html);
        $this->assertStringContainsString('Page 1 of 2', $html);
        $this->assertStringContainsString('Per page', $html);
    }

    public function test_app_locale_translates_table_labels(): void
    {
        app()->setLocale('id');

        $html = $this->renderTable()->render();

        $this->assertStringContainsString('Cari...', $html);
        $this->assertStringContainsString('Sebelumnya', $html);
        $this->assertStringContainsString('Berikutnya', $html);
        $this->assertStringContainsString('Halaman 1 dari 2', $html);
        $this->assertStringContainsString('Per halaman', $html);
    }

    public function test_per_table_locale_overrides_the_app_locale(): void
    {
        app()->setLocale('en');

        $html = $this->renderTable('id')->render();

        $this->assertStringContainsString('Cari...', $html);
        $this->assertStringContainsString('Halaman 1 dari 2', $html);
    }

    public function test_search_placeholder_can_be_overridden_per_table(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->searchPlaceholder('Find user...')
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $input = $this->makeXpath($html)->query("//input[@name='search']")->item(0);

        $this->assertInstanceOf(DOMElement::class, $input);
        $this->assertSame('Find user...', $input->getAttribute('placeholder'));
    }

    public function test_an_empty_search_placeholder_is_allowed(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->searchPlaceholder('')
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $input = $this->makeXpath($html)->query("//input[@name='search']")->item(0);

        $this->assertInstanceOf(DOMElement::class, $input);
        $this->assertTrue($input->hasAttribute('placeholder'));
        $this->assertSame('', $input->getAttribute('placeholder'));
    }

    public function test_empty_state_is_translated(): void
    {
        $html = HtmxTable::make(DB::table('items')->where('id', '<', 0))
            ->columns([Column::make('name')])
            ->locale('id')
            ->paginate(10)
            ->render(Request::create('/items'))
            ->render();

        $this->assertStringContainsString('Tidak ada data ditemukan.', $html);
    }

    public function test_unknown_locale_falls_back_to_the_fallback_locale(): void
    {
        $html = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->locale('fr')
            ->paginate(1)
            ->render(Request::create('/items'))
            ->render();

        $this->assertStringContainsString('Search...', $html);
    }

    private function renderTable(?string $locale = null): View
    {
        $table = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')->searchable()])
            ->perPageOptions([1, 2])
            ->paginate(1);

        if ($locale !== null) {
            $table->locale($locale);
        }

        return $table->render(Request::create('/items'));
    }
}
