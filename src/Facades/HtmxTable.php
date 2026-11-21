<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ahmaruff\HtmxTable\HtmxTable
 */
class HtmxTable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ahmaruff\HtmxTable\HtmxTable::class;
    }
}
