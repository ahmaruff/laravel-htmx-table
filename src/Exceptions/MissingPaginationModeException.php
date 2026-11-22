<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Exceptions;

use InvalidArgumentException;

final class MissingPaginationModeException extends InvalidArgumentException
{
    private ?string $context = null;

    public static function missing(?string $context = null): static
    {
        $message = 'Pagination mode must be configured (e.g., paginate(), simplePaginate(), or cursorPaginate()) before executing pagination.';

        if ($context !== null && $context !== '') {
            $message .= " Context: {$context}.";
        }

        $exception = new self($message);
        $exception->context = $context;

        return $exception;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }
}
