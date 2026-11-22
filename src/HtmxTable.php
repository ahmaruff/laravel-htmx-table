<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Ahmaruff\HtmxTable\Exceptions\InvalidColumnException;
use Ahmaruff\HtmxTable\Exceptions\MissingPaginationModeException;
use Illuminate\Contracts\View\Factory;
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

    private int $paginationOnEachSide;

    private ?string $id = null;

    /** @var view-string|null */
    private ?string $view = null;

    private ?string $locale = null;

    private ?string $searchPlaceholder = null;

    /** @var list<string> */
    private array $classes = [];

    /** @var list<int> */
    private array $perPageOptions = [];

    public const SEARCH_HEADER = 'X-Htmx-Table-Search'; // @pest-ignore-type

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

        $configuredOnEachSide = config('htmx-table.pagination.on_each_side', 2);
        $this->paginationOnEachSide = is_int($configuredOnEachSide) && $configuredOnEachSide >= 0
            ? $configuredOnEachSide
            : 2;
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

    public function classes(string ...$classes): static
    {
        foreach ($classes as $class) {
            $parts = preg_split('/\s+/', trim($class));

            if ($parts === false) {
                continue;
            }

            foreach ($parts as $part) {
                if ($part !== '' && ! in_array($part, $this->classes, true)) {
                    $this->classes[] = $part;
                }
            }
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param  view-string  $view
     */
    public function view(string $view): static
    {
        if (trim($view) === '') {
            throw new InvalidArgumentException('Table view must be a non-empty string.');
        }

        $this->view = $view;

        return $this;
    }

    /**
     * @return view-string
     */
    public function getView(): string
    {
        return $this->view ?? 'htmx-table::table';
    }

    public function locale(?string $locale): static
    {
        $locale = $locale !== null ? trim($locale) : null;

        $this->locale = $locale !== null && $locale !== '' ? $locale : null;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale ?? app()->getLocale();
    }

    public function searchPlaceholder(?string $placeholder): static
    {
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    public function getSearchPlaceholder(): ?string
    {
        return $this->searchPlaceholder;
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

        $this->setPerPage($perPage);

        return $this;
    }

    public function simplePaginate(?int $perPage = null): static
    {
        $this->paginationMode = self::PAG_MODE_SIMPLE;

        $this->setPerPage($perPage);

        return $this;
    }

    public function cursorPaginate(?int $perPage = null): static
    {
        $this->paginationMode = self::PAG_MODE_CURSOR;

        $this->setPerPage($perPage);

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

    /**
     * @param  array<array-key, mixed>  $options
     */
    public function perPageOptions(array $options): static
    {
        $validated = [];

        foreach ($options as $option) {
            if (! is_int($option) || $option < 1) {
                throw new InvalidArgumentException('Per-page options must be positive integers.');
            }

            if (! in_array($option, $validated, true)) {
                $validated[] = $option;
            }
        }

        $this->perPageOptions = $validated;

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getPerPageOptions(): array
    {
        if ($this->perPageOptions === []) {
            return [];
        }

        $options = $this->perPageOptions;

        if (! in_array($this->paginationPerPage, $options, true)) {
            array_unshift($options, $this->paginationPerPage);
        }

        return $options;
    }

    private function getOnEachSide(): int
    {
        return $this->paginationOnEachSide;
    }

    public static function isFragmentRequest(Request $request): bool
    {
        $isHtmxRequest = $request->header('HX-Request') !== null;
        $isHistoryRestoreRequest = $request->header('HX-History-Restore-Request') !== null;

        return $isHtmxRequest && ! $isHistoryRestoreRequest;
    }

    public function render(?Request $request = null): View
    {
        $request ??= request();

        $mode = $this->getPaginationMode();
        $state = TableState::fromRequest($request, $mode);
        $perPage = $this->resolvePerPage($state->perPage);
        $state = $state->withPerPage($this->getPerPageOptions() !== [] ? $perPage : null);

        $engine = new QueryEngine(
            $this->getBuilder(),
            $this->getColumns(),
            $state,
            $perPage,
        );

        $paginator = $engine->paginate();

        if ($mode === self::PAG_MODE_PAGINATE && method_exists($paginator, 'onEachSide')) {
            $paginator->onEachSide($this->getOnEachSide());
        }

        $locale = $this->getLocale();

        return app(Factory::class)->make($this->getView(), [
            'rows' => $paginator,
            'columns' => $this->getColumns(),
            'paginationMode' => $mode,
            'state' => $state,
            'id' => $this->getId(),
            'classes' => $this->getClasses(),
            'perPage' => $perPage,
            'perPageOptions' => $this->getPerPageOptions(),
            'locale' => $locale,
            'focusSearch' => $request->header(self::SEARCH_HEADER) !== null,
            'searchPlaceholder' => $this->getSearchPlaceholder()
                ?? (string) trans('htmx-table::messages.search.placeholder', [], $locale),
        ]);
    }

    private function setPerPage(?int $perPage): void
    {
        if ($perPage === null) {
            return;
        }

        if ($perPage < 1) {
            throw new InvalidArgumentException('Per-page value must be a positive integer.');
        }

        $this->paginationPerPage = $perPage;
    }

    private function resolvePerPage(?int $requestedPerPage): int
    {
        $options = $this->getPerPageOptions();

        if ($options === []) {
            return $this->paginationPerPage;
        }

        if ($requestedPerPage !== null && in_array($requestedPerPage, $options, true)) {
            return $requestedPerPage;
        }

        return $this->paginationPerPage;
    }
}
