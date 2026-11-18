<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;

class MergeProp implements Mergeable
{
    use MergesProps;
    use ResolvesCallables;

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
