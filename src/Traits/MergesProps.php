<?php

namespace EvoMark\InertiaWordpress\Traits;

use Illuminate\Support\Arr;

trait MergesProps
{
    protected bool $merge = false;

    protected bool $deepMerge = false;

    /** @var array<int, string> */
    protected array $matchOn = [];

    protected bool $append = true;

    /** @var array<int, string> */
    protected array $appendsAtPaths = [];

    /** @var array<int, string> */
    protected array $prependsAtPaths = [];

    public function merge(): static
    {
        $this->merge = true;

        return $this;
    }

    /**
     * Recursively merge objects on the client, rather than appending to a list.
     */
    public function deepMerge(): static
    {
        $this->deepMerge = true;

        return $this->merge();
    }

    /**
     * Key(s) used to match existing items when merging, so they are updated rather than duplicated.
     *
     * @param  string|array<int, string>  $matchOn
     */
    public function matchOn(string|array $matchOn): static
    {
        $this->matchOn = Arr::wrap($matchOn);

        return $this;
    }

    public function shouldMerge(): bool
    {
        return $this->merge;
    }

    public function shouldDeepMerge(): bool
    {
        return $this->deepMerge;
    }

    /**
     * @return array<int, string>
     */
    public function matchesOn(): array
    {
        return $this->matchOn;
    }

    public function appendsAtRoot(): bool
    {
        return $this->append && $this->mergesAtRoot();
    }

    public function prependsAtRoot(): bool
    {
        return ! $this->append && $this->mergesAtRoot();
    }

    protected function mergesAtRoot(): bool
    {
        return count($this->appendsAtPaths) === 0 && count($this->prependsAtPaths) === 0;
    }

    /**
     * Append new items. With no arguments, appends to the prop itself; a path (or list of
     * paths, optionally keyed by path => matchOn key) appends at that nested location.
     *
     * @param  bool|string|array<int|string, string>  $path
     */
    public function append(bool|string|array $path = true, ?string $matchOn = null): static
    {
        match (true) {
            is_bool($path) => $this->append = $path,
            is_string($path) => $this->appendsAtPaths[] = $path,
            is_array($path) => collect($path)->each(
                fn ($value, $key) => is_numeric($key) ? $this->append($value) : $this->append($key, $value)
            ),
        };

        if (is_string($path) && $matchOn) {
            $this->matchOn([...$this->matchOn, "{$path}.{$matchOn}"]);
        }

        return $this;
    }

    /**
     * Prepend new items. Takes the same arguments as append().
     *
     * @param  bool|string|array<int|string, string>  $path
     */
    public function prepend(bool|string|array $path = true, ?string $matchOn = null): static
    {
        match (true) {
            is_bool($path) => $this->append = ! $path,
            is_string($path) => $this->prependsAtPaths[] = $path,
            is_array($path) => collect($path)->each(
                fn ($value, $key) => is_numeric($key) ? $this->prepend($value) : $this->prepend($key, $value)
            ),
        };

        if (is_string($path) && $matchOn) {
            $this->matchOn([...$this->matchOn, "{$path}.{$matchOn}"]);
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function appendsAtPaths(): array
    {
        return $this->appendsAtPaths;
    }

    /**
     * @return array<int, string>
     */
    public function prependsAtPaths(): array
    {
        return $this->prependsAtPaths;
    }
}
