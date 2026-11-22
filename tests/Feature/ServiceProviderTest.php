<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\Column;
use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\HtmxTableServiceProvider;
use Ahmaruff\HtmxTable\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

final class ServiceProviderTest extends TestCase
{
    public function test_publish_tags_expose_config_views_and_assets(): void
    {
        $config = ServiceProvider::pathsToPublish(HtmxTableServiceProvider::class, 'htmx-table-config');
        $views = ServiceProvider::pathsToPublish(HtmxTableServiceProvider::class, 'htmx-table-views');
        $assets = ServiceProvider::pathsToPublish(HtmxTableServiceProvider::class, 'htmx-table-assets');
        $all = ServiceProvider::pathsToPublish(HtmxTableServiceProvider::class, 'htmx-table');

        $this->assertContains(config_path('htmx-table.php'), array_values($config));
        $this->assertContains(resource_path('views/vendor/htmx-table'), array_values($views));
        $this->assertContains(public_path('vendor/htmx-table/htmx-table.css'), array_values($assets));
        $this->assertCount(3, $all);
    }

    public function test_the_blade_component_is_registered(): void
    {
        $namespaces = Blade::getAnonymousComponentNamespaces();

        $this->assertSame('htmx-table::components', $namespaces['htmx-table'] ?? null);
    }

    public function test_the_blade_component_renders_a_table(): void
    {
        DB::table('items')->insert([
            ['id' => 1, 'name' => 'Alpha', 'status' => 'active', 'secret_code' => 'S-1'],
        ]);

        $table = HtmxTable::make(DB::table('items'))
            ->columns([Column::make('name')])
            ->paginate(10);

        $html = Blade::render('<x-htmx-table::table :table="$table" />', ['table' => $table]);

        $this->assertStringContainsString('htmx-table-wrapper', $html);
        $this->assertStringContainsString('Alpha', $html);
    }
}
