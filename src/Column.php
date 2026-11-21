<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable;

use Illuminate\Support\Str;

class Column
{
    private string $key;
    private ?string $dataKey = null;
    private ?string $label = null;
    private bool $sortable = false;
    private bool $searchable = false;
    private mixed $renderer = null;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function getKey(): string
    {
        return $this->key;        
    }

    public function label(?string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): string
    {
        if(is_null($this->label)) {
            return Str::of($this->key)->afterLast(".")->headline()->toString();
        }

        return $this->label;
    }

    public function dataKey(?string $key): static
    {
        $this->dataKey = $key;
        return $this;
    }

    public function getDataKey(): string
    {
        return $this->dataKey ?? $this->key;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function render(callable $renderer): static
    {
        $this->renderer = $renderer;
        return $this;
    }

    public function hasRenderer(): bool
    {
        return !is_null($this->renderer);
    }

    public function resolveValue(mixed $row): mixed
    {
        return data_get($row, $this->getDataKey());
    }

    public function resolveCell(mixed $row): mixed
    {
        $value = $this->resolveValue($row);
        
        if($this->hasRenderer()) {
            return ($this->renderer)($row, $value);
        }
        return $value;
    }
}