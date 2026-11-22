<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\Cursor;
use InvalidArgumentException;
use Throwable;

class QueryEngine
{
    /**
     * @param  EloquentBuilder<Model>|QueryBuilder  $builder
     * @param  list<Column>  $columns
     */
    public function __construct(
        private EloquentBuilder|QueryBuilder $builder,
        private array $columns,
        private TableState $state,
        private ?int $perPage = null,
    ) {}

    /**
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function apply(): EloquentBuilder|QueryBuilder
    {
        $this->applySearch($this->builder);
        $this->applySort($this->builder);

        return $this->builder;
    }

    // Laravel 10 and 12+ declare different paginator template arity.
    public function paginate(): Paginator|CursorPaginator // @phpstan-ignore missingType.generics, missingType.generics
    {
        $builder = $this->apply();
        $perPage = $this->perPage;
        $mode = $this->state->paginationMode();

        return match ($mode) {
            HtmxTable::PAG_MODE_PAGINATE => $builder->paginate(
                $perPage,
                ['*'],
                $this->state->alias('page'),
                $this->state->page ?? 1,
            ),
            HtmxTable::PAG_MODE_SIMPLE => $builder->simplePaginate(
                $perPage,
                ['*'],
                $this->state->alias('page'),
                $this->state->page ?? 1,
            ),
            HtmxTable::PAG_MODE_CURSOR => $builder->cursorPaginate(
                $perPage,
                ['*'],
                $this->state->alias('cursor'),
                self::decodeCursor($this->state->cursor),
            ),
            default => throw new InvalidArgumentException("Unsupported pagination mode [{$mode}]."),
        };
    }

    /**
     * @param  EloquentBuilder<Model>|QueryBuilder  $builder
     */
    private function applySearch(EloquentBuilder|QueryBuilder $builder): void
    {
        $search = $this->state->search;

        if ($search === '') {
            return;
        }

        /** @var list<Column> $searchable */
        $searchable = [];

        foreach ($this->columns as $column) {
            if ($column->isSearchable()) {
                $searchable[] = $column;
            }
        }

        if ($searchable === []) {
            return;
        }

        $builder->where(function (EloquentBuilder|QueryBuilder $query) use ($searchable, $search): void {
            foreach ($searchable as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $query->{$method}($column->getDataKey(), 'like', "%{$search}%");
            }
        });
    }

    /**
     * @param  EloquentBuilder<Model>|QueryBuilder  $builder
     */
    private function applySort(EloquentBuilder|QueryBuilder $builder): void
    {
        $sort = $this->state->sort;
        $dir = $this->state->dir;

        $tieBreaker = $builder instanceof EloquentBuilder
            ? $builder->getModel()->getKeyName()
            : 'id';

        if ($sort === '') {
            if ($this->state->paginationMode() === HtmxTable::PAG_MODE_CURSOR) {
                $builder->orderBy($tieBreaker, 'asc');
            }

            return;
        }

        $matchedColumn = null;

        foreach ($this->columns as $column) {
            if ($column->isSortable() && $column->getKey() === $sort) {
                $matchedColumn = $column;

                break;
            }
        }

        if ($matchedColumn === null) {
            if ($this->state->paginationMode() === HtmxTable::PAG_MODE_CURSOR) {
                $builder->orderBy($tieBreaker, 'asc');
            }

            return;
        }

        $builder->orderBy($matchedColumn->getDataKey(), $dir);

        if ($this->state->paginationMode() === HtmxTable::PAG_MODE_CURSOR) {
            $sortField = $matchedColumn->getDataKey();
            $unqualifiedSort = str_contains($sortField, '.')
                ? substr($sortField, strrpos($sortField, '.') + 1)
                : $sortField;

            if ($unqualifiedSort !== $tieBreaker) {
                $builder->orderBy($tieBreaker, $dir);
            }
        }
    }

    private static function decodeCursor(?string $encodedCursor): ?Cursor
    {
        if ($encodedCursor === null || $encodedCursor === '') {
            return null;
        }

        try {
            return Cursor::fromEncoded($encodedCursor);
        } catch (Throwable) {
            return null;
        }
    }
}
