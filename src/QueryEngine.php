<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Pagination\Cursor;

class QueryEngine
{
    /**
     * @param list<Column> $columns
     */
    public function __construct(
        private EloquentBuilder|QueryBuilder $builder,
        private array $columns,
        private string $paginationMode,
        private ?int $perPage = null,
    ) {}

    public function apply(Request $request): EloquentBuilder|QueryBuilder
    {
        $this->applySearch($this->builder, $request);
        $this->applySort($this->builder, $request);

        return $this->builder;
    }

    public function paginate(Request $request): Paginator|CursorPaginator
    {
        $builder = $this->apply($request);
        $perPage = $this->perPage;
        $pagination = self::getPaginationValue($request, $this->paginationMode);

        return match ($this->paginationMode) {
            HtmxTable::PAG_MODE_PAGINATE => $builder->paginate(
                $perPage,
                ['*'],
                self::queryParam('page', 'page'),
                $pagination['page'] ?? 1,
            ),
            HtmxTable::PAG_MODE_SIMPLE => $builder->simplePaginate(
                $perPage,
                ['*'],
                self::queryParam('page', 'page'),
                $pagination['page'] ?? 1,
            ),
            HtmxTable::PAG_MODE_CURSOR => $builder->cursorPaginate(
                $perPage,
                ['*'],
                self::queryParam('cursor', 'cursor'),
                self::decodeCursor($pagination['cursor']),
            ),
        };
    }

    private function applySearch(EloquentBuilder|QueryBuilder $builder, Request $request): void
    {
        $search = self::getSearchValue($request);
        if($search == "") {
            return;
        }

        /** @var list<Column> $searchable */
        $searchable = [];

        foreach ($this->columns as $column) {
            if (!$column instanceof Column) {
                continue;
            }

            if($column->isSearchable()) {
                $searchable[] = $column;
            }
        }

        if ($searchable === []) {
            return;
        }

        $builder->where(function ($query) use ($searchable, $search): void {
            foreach ($searchable as $index => $column) {
                $field = $column->getDataKey();
                $method = $index === 0 ? 'where' : 'orWhere';

                $query->{$method}($field, 'like', "%{$search}%");
            }
        });
    }

    private function applySort(EloquentBuilder|QueryBuilder $builder, Request $request): void
    {
        $sortState = self::getSortValue($request);

        $sort = $sortState['sort'] ?? "";
        $dir = $sortState['dir'] ?? "asc";

        if ($sort === '') {
            return;
        }

        /** @var Column|null $matchedColumn */
        $matchedColumn = null;

        foreach ($this->columns as $column) {
            if ($column instanceof Column && $column->isSortable() && $column->getKey() === $sort) {
                $matchedColumn = $column;
                break;
            }
        }

        if ($matchedColumn === null) {
            return;
        }

        $builder->orderBy($matchedColumn->getDataKey(), $dir);

        if ($this->paginationMode === HtmxTable::PAG_MODE_CURSOR) {
            $tieBreaker = $builder instanceof EloquentBuilder
                ? $builder->getModel()->getKeyName()
                : 'id';

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
        } catch (\Throwable) {
            return null;
        }
    }

    public static function queryParam(string $key, string $default): string
    {
        $value = config("htmx-table.query_params.{$key}", $default);

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    }

    public static function getSearchValue(Request $request): ?string
    {
        $param = self::queryParam('search', 'search');
        $rawSearch = $request->query($param, '');

        return is_scalar($rawSearch) ? trim((string) $rawSearch) : '';
    }

    /**
     * @return array{sort: string, dir: 'asc'|'desc'}
     */
    public static function getSortValue(Request $request): array
    {
        $sortParam = self::queryParam('sort', 'sort');
        $dirParam = self::queryParam('dir', 'dir');

        $rawSort = $request->query($sortParam, '');
        $sort = is_scalar($rawSort) ? trim((string) $rawSort) : '';

        $rawDir = $request->query($dirParam, 'asc');
        $dir = is_scalar($rawDir) ? strtolower(trim((string) $rawDir)) : 'asc';

        /** @var 'asc'|'desc' $safeDir */
        $safeDir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc';

        return [
            'sort' => $sort,
            'dir' => $safeDir,
        ];
    }

    /**
     * @return array{page: ?int, cursor: ?string}
     */
    public static function getPaginationValue(Request $request, string $paginationMode): array
    {
        if ($paginationMode === HtmxTable::PAG_MODE_CURSOR) {
            $cursorParam = self::queryParam('cursor', 'cursor');
            $rawCursor = $request->query($cursorParam);
            $cursor = is_scalar($rawCursor) ? trim((string) $rawCursor) : '';

            return [
                'page' => null,
                'cursor' => $cursor !== '' ? $cursor : null,
            ];
        }

        $pageParam = self::queryParam('page', 'page');
        $rawPage = $request->query($pageParam, 1);
        $page = filter_var($rawPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return [
            'page' => $page !== false ? $page : 1,
            'cursor' => null,
        ];
    }
}