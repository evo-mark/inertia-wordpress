<?php

namespace EvoMark\InertiaWordpress\Tests\Stubs;

use EvoMark\InertiaWordpress\Contracts\ProvidesScrollMetadata;

final class StaticScrollMetadata implements ProvidesScrollMetadata
{
    public function __construct(
        private string $pageName = 'paged',
        private int|string|null $previous = null,
        private int|string|null $next = 2,
        private int|string|null $current = 1,
    ) {
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    public function getPreviousPage(): int|string|null
    {
        return $this->previous;
    }

    public function getNextPage(): int|string|null
    {
        return $this->next;
    }

    public function getCurrentPage(): int|string|null
    {
        return $this->current;
    }
}
