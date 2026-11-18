<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;

class OptionalProp implements IgnoreFirstLoad
{
    use ResolvesCallables;

    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke()
    {
        return $this->resolveCallable($this->callback);
    }
}
