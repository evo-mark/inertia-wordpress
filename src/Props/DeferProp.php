<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;

class DeferProp implements IgnoreFirstLoad, Mergeable
{
    use MergesProps;
    use ResolvesCallables;

    protected $callback;

    protected $group;

    public function __construct(callable $callback, string $group = 'default')
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
        return $this->resolveCallable($this->callback);
    }
}
