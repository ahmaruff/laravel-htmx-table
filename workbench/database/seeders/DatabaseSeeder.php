<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['active', 'pending', 'archived'];
        $now = now();
        $rows = [];

        for ($i = 1; $i <= 25; $i++) {
            $rows[] = [
                'name' => sprintf('Item %02d (%s)', $i, chr(64 + (($i - 1) % 26) + 1)),
                'status' => $statuses[$i % count($statuses)],
                'sku' => sprintf('SKU-%04d', $i),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('items')->upsert(
            $rows,
            ['sku'],
            ['name', 'status', 'updated_at'],
        );
    }
}