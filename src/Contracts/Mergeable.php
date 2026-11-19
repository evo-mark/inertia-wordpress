<?php

namespace EvoMark\InertiaWordpress\Contracts;

/**
 * A prop the client should merge into its existing value instead of replacing.
 */
interface Mergeable
{
    public function merge();

    public function shouldMerge();

    public function shouldDeepMerge();

    /**
     * Keys (relative to the prop) used to match items when merging, e.g. ["data.id"].
     */
    public function matchesOn();

    public function appendsAtRoot();

    public function prependsAtRoot();

    /**
     * Nested paths (relative to the prop) to append at, e.g. ["data"].
     */
    public function appendsAtPaths(): array;

    /**
     * Nested paths (relative to the prop) to prepend at.
     */
    public function prependsAtPaths(): array;
}
