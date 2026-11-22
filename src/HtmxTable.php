<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Ahmaruff\HtmxTable\Exceptions\InvalidColumnException;
use Ahmaruff\HtmxTable\Exceptions\MissingPaginationModeException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * @phpstan-consistent-constructor
 */
class HtmxTable
{
    /** @var EloquentBuilder<Model>|QueryBuilder */
    private EloquentBuilder|QueryBuilder $builder;

    /** @var list<Column> */
    private array $columns = [];

    private ?string $paginationMode = null;

    private int $paginationPerPage;

    private ?string $id = null;

    public const PAG_MODE_PAGINATE = 'paginate'; // @pest-ignore-type

    public const PAG_MODE_SIMPLE = 'simple-paginate'; // @pest-ignore-type

    public const PAG_MODE_CURSOR = 'cursor'; // @pest-ignore-type

    /**
     * @param  EloquentBuilder<Model>|QueryBuilder  $builder
     */
    public function __construct(EloquentBuilder|QueryBuilder $builder)
    {
        $this->builder = $builder;

        $configuredPerPage = config('htmx-table.per_page', 15);
        $this->paginationPerPage = is_int($configuredPerPage) && $configuredPerPage > 0
            ? $configuredPerPage
            : 15;
    }

    /**
     * @param  EloquentBuilder<Model>|QueryBuilder  $builder
     */
    public static function make(EloquentBuilder|QueryBuilder $builder): static
    {
        return new static($builder);
    }

    /**
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function getBuilder(): EloquentBuilder|QueryBuilder
    {
        return $this->builder;
    }

    public function id(string $id): static
    {
        $id = trim($id);

        if ($id === '') {
            throw new InvalidArgumentException('Table id must be a non-empty string.');
        }

        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param  array<array-key, mixed>  $columns
     */
    public function columns(array $columns): static
    {
        $validated = [];

        foreach ($columns as $col) {
            if (! $col instanceof Column) {
                throw InvalidColumnException::invalidItems($col);
            }

            $validated[] = $col;
        }

        $this->columns = $validated;

        return $this;
    }

    /**
     * @return list<Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function paginate(?int $perPage = null): static
    {
        $this->paginationMode = self::PAG_MODE_PAGINATE;

        if ($perPage !== null) {
            $this->paginationPerPage = $perPage;
        }

        return $this;
    }

    public function simplePaginate(?int $perPage = null): static
    {
        $this->paginationMode = self::PAG_MODE_SIMPLE;

        if ($perPage !== null) {
            $this->paginationPerPage = $perPage;
        }

        return $this;
    }

    public function cursorPaginate(?int $perPage = null): static
    {
        $this->paginationMode = self::PAG_MODE_CURSOR;

        if ($perPage !== null) {
            $this->paginationPerPage = $perPage;
        }

        return $this;
    }

    public function getPaginationMode(): string
    {
        if (is_null($this->paginationMode)) {
            throw MissingPaginationModeException::missing($this->paginationMode);
        }

        return $this->paginationMode;
    }

    public function getPerPage(): int
    {
        return $this->paginationPerPage;
    }

    public function render(?Request $request = null): View
    {
        $request ??= request();

        $mode = $this->getPaginationMode();
        $state = TableState::fromRequest($request, $mode);

        $engine = new QueryEngine(
            $this->getBuilder(),
            $this->getColumns(),
            $state,
            $this->getPerPage(),
        );

        $paginator = $engine->paginate();

        return view('htmx-table::table', [
            'rows' => $paginator,
            'columns' => $this->getColumns(),
            'paginationMode' => $mode,
            'state' => $state,
            'id' => $this->getId(),
        ]);
    }
}
