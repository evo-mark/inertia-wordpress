<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\ResolvesCallables;

class AlwaysProp
{
    use ResolvesCallables;

    /** @var mixed */
    protected $value;

    /**
     * @param  mixed  $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return $this->resolveCallable($this->value);
    }
}
