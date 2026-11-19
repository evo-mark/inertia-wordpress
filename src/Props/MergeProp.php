<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;
use EvoMark\InertiaWordpress\Traits\ResolvesOnce;

class MergeProp implements Mergeable, Onceable
{
    use MergesProps;
    use ResolvesCallables;
    use ResolvesOnce;

    /** @var mixed */
    protected $value;

    /**
     * @param  mixed  $value
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->merge = true;
    }

    public function __invoke()
    {
        return $this->resolveCallable($this->value);
    }
}
