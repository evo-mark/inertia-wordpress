<?php

namespace EvoMark\InertiaWordpress\Props;

class AlwaysProp
{
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
        return is_callable($this->value) ? call_user_func($this->value) : $this->value;
    }
}
