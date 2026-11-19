<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Contracts\Deferrable;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;
use EvoMark\InertiaWordpress\Traits\ResolvesOnce;

class DeferProp implements IgnoreFirstLoad, Mergeable, Onceable, Deferrable
{
    use MergesProps;
    use ResolvesCallables;
    use ResolvesOnce;

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

    public function shouldDefer(): bool
    {
        return true;
    }

    public function __invoke()
    {
        return $this->resolveCallable($this->callback);
    }
}
