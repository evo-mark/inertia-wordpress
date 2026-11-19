<?php

namespace EvoMark\InertiaWordpress\Contracts;

/**
 * A prop that can be left out of the first visit and fetched straight afterwards.
 */
interface Deferrable
{
    public function shouldDefer(): bool;

    /**
     * Deferred props in the same group are fetched together in one request.
     */
    public function group();
}
