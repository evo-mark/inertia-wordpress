<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;
use EvoMark\InertiaWordpress\Traits\ResolvesOnce;

/**
 * Included on the first visit, then cached by the client and skipped on later visits.
 */
class OnceProp implements Onceable
{
    use ResolvesCallables;
    use ResolvesOnce;

    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
        $this->once = true;
    }

    public function __invoke()
    {
        return $this->resolveCallable($this->callback);
    }
}
