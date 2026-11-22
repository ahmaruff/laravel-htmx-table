<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Http\Request;

final class TableState
{
    /**
     * @param  'asc'|'desc'  $dir
     * @param  array{search: string, sort: string, dir: string, page: string, cursor: string}  $aliases
     * @param  array<string, mixed>  $extraQuery
     */
    private function __construct(
        public readonly string $search,
        public readonly string $sort,
        public readonly string $dir,
        public readonly ?int $page,
        public readonly ?string $cursor,
        private readonly string $paginationMode,
        private readonly array $aliases,
        private readonly array $extraQuery,
    ) {}

    public static function fromRequest(Request $request, string $paginationMode): self
    {
        $aliases = self::aliases();

        /** @var array<string, mixed> $query */
        $query = $request->query();

        $search = self::stringValue($query[$aliases['search']] ?? '');
        $sort = self::stringValue($query[$aliases['sort']] ?? '');

        $dir = self::direction($query[$aliases['dir']] ?? 'asc');

        $page = null;
        $cursor = null;

        if ($paginationMode === HtmxTable::PAG_MODE_CURSOR) {
            $rawCursor = self::stringValue($query[$aliases['cursor']] ?? '');
            $cursor = $rawCursor !== '' ? $rawCursor : null;
        } else {
            $rawPage = $query[$aliases['page']] ?? 1;
            $page = filter_var($rawPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($page === false) {
                $page = 1;
            }
        }

        $extraQuery = $query;

        foreach ($aliases as $alias) {
            unset($extraQuery[$alias]);
        }

        return new self(
            search: $search,
            sort: $sort,
            dir: $dir,
            page: $page,
            cursor: $cursor,
            paginationMode: $paginationMode,
            aliases: $aliases,
            extraQuery: $extraQuery,
        );
    }

    /**
     * @return array{search: string, sort: string, dir: string, page: string, cursor: string}
     */
    public static function aliases(): array
    {
        return [
            'search' => self::queryParam('search', 'search'),
            'sort' => self::queryParam('sort', 'sort'),
            'dir' => self::queryParam('dir', 'dir'),
            'page' => self::queryParam('page', 'page'),
            'cursor' => self::queryParam('cursor', 'cursor'),
        ];
    }

    public static function queryParam(string $key, string $default): string
    {
        $value = config("htmx-table.query_params.{$key}", $default);

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    }

    public function alias(string $key): string
    {
        return $this->aliases[$key] ?? $key;
    }

    public function paginationMode(): string
    {
        return $this->paginationMode;
    }

    /**
     * @param  array{search?: string|null, sort?: string|null, dir?: string|null, page?: int|string|null, cursor?: string|null}  $overrides
     */
    public function url(string $baseUrl, array $overrides = []): string
    {
        $search = $overrides['search'] ?? ($this->search !== '' ? $this->search : null);
        $sort = $overrides['sort'] ?? ($this->sort !== '' ? $this->sort : null);
        $dir = $overrides['dir'] ?? ($sort !== null && $sort !== '' ? $this->dir : null);
        $page = $overrides['page'] ?? null;
        $cursor = $overrides['cursor'] ?? null;

        $query = $this->extraQuery;

        foreach ([
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
            'page' => $page,
            'cursor' => $cursor,
        ] as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $query[$this->alias($key)] = $value;
        }

        return self::buildUrl($baseUrl, $query);
    }

    public function applyToUrl(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $existingQuery = [];

        if (isset($parts['query'])) {
            parse_str($parts['query'], $existingQuery);
        }

        $query = array_merge($this->extraQuery, $existingQuery);

        foreach (['search', 'sort', 'dir'] as $key) {
            unset($query[$this->alias($key)]);
        }

        $state = [
            'search' => $this->search !== '' ? $this->search : null,
            'sort' => $this->sort !== '' ? $this->sort : null,
            'dir' => $this->sort !== '' ? $this->dir : null,
        ];

        foreach ($state as $key => $value) {
            if ($value === null) {
                continue;
            }

            $query[$this->alias($key)] = $value;
        }

        $scheme = isset($parts['scheme']) ? "{$parts['scheme']}://" : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ":{$parts['port']}" : '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? "#{$parts['fragment']}" : '';
        $queryString = http_build_query($query);

        $resolved = "{$scheme}{$host}{$port}{$path}";

        if ($queryString !== '') {
            $resolved .= "?{$queryString}";
        }

        return $resolved.$fragment;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function buildUrl(string $baseUrl, array $query): string
    {
        $queryString = http_build_query($query);

        return $queryString !== '' ? "{$baseUrl}?{$queryString}" : $baseUrl;
    }

    /**
     * @return 'asc'|'desc'
     */
    private static function direction(mixed $value): string
    {
        return strtolower(self::stringValue($value)) === 'desc' ? 'desc' : 'asc';
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
