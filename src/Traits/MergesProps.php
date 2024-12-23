<?php

namespace EvoMark\InertiaWordpress\Traits;

trait MergesProps
{
    protected bool $merge = false;

    public function merge(): static
    {
        $this->merge = true;

        return $this;
    }

    public function shouldMerge(): bool
    {
        return $this->merge;
    }
}
