<?php

namespace EvoMark\InertiaWordpress\Tests\Stubs;

final class InvokableValue
{
    public function __construct(private mixed $value)
    {
    }

    public function __invoke(): mixed
    {
        return $this->value;
    }
}
