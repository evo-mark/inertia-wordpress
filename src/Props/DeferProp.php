<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;

class DeferProp implements IgnoreFirstLoad, Mergeable
{
    use MergesProps;

    protected $callback;

    protected $group;

    public function __construct(callable $callback, ?string $group = null)
    {
        $this->callback = $callback;
        $this->group = $group;
    }

    public function group()
    {
        return $this->group;
    }

    public function __invoke()
    {
        return call_user_func($this->callback);
    }
}
