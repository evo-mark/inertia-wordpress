<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Contracts\Mergeable;

class MergeProp implements Mergeable
{
    use MergesProps;

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
        return is_callable($this->value) ? call_user_func($this->value) : $this->value;
    }
}
