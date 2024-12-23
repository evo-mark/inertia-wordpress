<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;

class LazyProp implements IgnoreFirstLoad
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke()
    {
        return call_user_func($this->callback);
    }
}
