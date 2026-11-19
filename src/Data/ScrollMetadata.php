<?php

namespace EvoMark\InertiaWordpress\Data;

use InvalidArgumentException;
use EvoMark\InertiaWordpress\Contracts\ProvidesScrollMetadata;

class ScrollMetadata implements ProvidesScrollMetadata
{
    public function __construct(
        protected string $pageName,
        protected int|string|null $previousPage = null,
        protected int|string|null $nextPage = null,
        protected int|string|null $currentPage = null,
    ) {
    }

    /**
     * Pagination details from a WP_Query: "paged" is the page query var, and
     * max_num_pages the last page.
     */
    public static function fromQuery(mixed $query): self
    {
        if (! $query instanceof \WP_Query) {
            throw new InvalidArgumentException('The given value is not a WP_Query instance. Use a custom callback to extract pagination metadata.');
        }

        $current = max(1, (int) $query->get('paged'));
        $last = (int) $query->max_num_pages;

        return new self(
            'paged',
            $current > 1 ? $current - 1 : null,
            $current < $last ? $current + 1 : null,
            $current,
        );
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    public function getPreviousPage(): int|string|null
    {
        return $this->previousPage;
    }

    public function getNextPage(): int|string|null
    {
        return $this->nextPage;
    }

    public function getCurrentPage(): int|string|null
    {
        return $this->currentPage;
    }

    /**
     * @return array{pageName: string, previousPage: int|string|null, nextPage: int|string|null, currentPage: int|string|null}
     */
    public function toArray(): array
    {
        return [
            'pageName' => $this->getPageName(),
            'previousPage' => $this->getPreviousPage(),
            'nextPage' => $this->getNextPage(),
            'currentPage' => $this->getCurrentPage(),
        ];
    }
}
