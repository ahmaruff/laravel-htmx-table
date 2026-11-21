<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

use Ahmaruff\HtmxTable\Exceptions\InvalidColumnException;
use Ahmaruff\HtmxTable\Exceptions\MissingPaginationModeException;

class HtmxTable
{
    private EloquentBuilder|QueryBuilder $builder;

    /** @var list<Column> */
    private array $columns = [];
    private ?string $paginationMode = null;
    private ?int $paginationPerPage = null;

    public const PAG_MODE_PAGINATE = "paginate";
    public const PAG_MODE_SIMPLE = "simple-paginate";
    public const PAG_MODE_CURSOR = "cursor";

    public function __construct(EloquentBuilder|QueryBuilder $builder)
    {
        $this->builder = $builder;

        $configuredPerPage = config('laravel-htmx-table.per_page', 15);
        $this->paginationPerPage = is_int($configuredPerPage) && $configuredPerPage > 0
            ? $configuredPerPage
            : 15;
    }

    public static function make(EloquentBuilder|QueryBuilder $builder): static
    {
        return new static($builder);
    }

    public function getBuilder(): EloquentBuilder|QueryBuilder
    {
        return $this->builder;
    }

    public function columns(array $columns): static
    {
        foreach ($columns as $col) {
            if(!$col instanceof Column) {
                throw InvalidColumnException::invalidItems($col);
            }
        }

        $this->columns = $columns;
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
        if(is_null($this->paginationMode)) {
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

        $engine = new QueryEngine(
            $this->getBuilder(),
            $this->getColumns(),
            $this->getPaginationMode(),
            $this->getPerPage(),
        );


        $paginator = $engine->paginate($request);

        $mode = $this->getPaginationMode();
        $sortState = QueryEngine::getSortValue($request);
        $paginationState = QueryEngine::getPaginationValue($request, $mode);

        return view('htmx-table::table', [
            'rows' => $paginator,
            'columns' => $this->getColumns(),
            'paginationMode' => $mode,
            'perPage' => $this->getPerPage(),
            'state' => [
                'search' => QueryEngine::getSearchValue($request) ?? '',
                'sort' => $sortState['sort'],
                'dir' => $sortState['dir'],
                'page' => $paginationState['page'],
                'cursor' => $paginationState['cursor'],
            ],
        ]);
    }
}
