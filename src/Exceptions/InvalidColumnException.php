<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Exceptions;

use Ahmaruff\HtmxTable\Column;
use InvalidArgumentException;

/**
 * @phpstan-consistent-constructor
 */
class InvalidColumnException extends InvalidArgumentException
{
    private mixed $items = null;

    public static function invalidItems(mixed $items): static
    {
        $type = get_debug_type($items);
        $expected = Column::class;

        $exception = new static(
            "Every table column must be an instance of {$expected}, {$type} given.",
        );
        $exception->items = $items;

        return $exception;
    }

    public function setItems(mixed $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getItems(): mixed
    {
        return $this->items;
    }
}
