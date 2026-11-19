<?php

namespace EvoMark\InertiaWordpress\Contracts;

/**
 * Pagination details for an infinite scroll prop, used by the client's <InfiniteScroll>.
 */
interface ProvidesScrollMetadata
{
    /**
     * The query parameter the client sets to request a page, e.g. "paged".
     */
    public function getPageName(): string;

    public function getPreviousPage(): int|string|null;

    public function getNextPage(): int|string|null;

    public function getCurrentPage(): int|string|null;
}
