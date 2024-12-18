<?php

namespace EvoMark\InertiaWordpress\Contracts;

interface Mergeable
{
    public function merge();

    public function shouldMerge();
}
