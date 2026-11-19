<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;
use EvoMark\InertiaWordpress\Traits\ResolvesOnce;

class OptionalProp implements IgnoreFirstLoad, Onceable
{
    use ResolvesCallables;
    use ResolvesOnce;

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
