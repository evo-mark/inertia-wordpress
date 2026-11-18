<?php

namespace EvoMark\InertiaWordpress\Traits;

trait ResolvesCallables
{
    /**
     * Call the given value if it should be treated as a callable, otherwise return it untouched.
     */
    protected function resolveCallable(mixed $value): mixed
    {
        return $this->useAsCallable($value) ? call_user_func($value) : $value;
    }

    /**
     * Only closures and invokable objects are called.
     *
     * Strings ("date", "Class::method") and [class, method] arrays are also is_callable(),
     * but they are data: prop values often carry site content (titles, menu names, form
     * labels), and calling them would let that content trigger arbitrary PHP functions.
     */
    protected function useAsCallable(mixed $value): bool
    {
        return is_object($value) && is_callable($value);
    }
}
