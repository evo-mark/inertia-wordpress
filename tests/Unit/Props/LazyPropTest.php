<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Props\LazyProp;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * LazyProp is the deprecated v1 name for OptionalProp; it is kept for backwards compatibility.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/LazyPropTest.php
 */
class LazyPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $lazyProp = new LazyProp(fn () => 'A lazy value');

        $this->assertSame('A lazy value', $lazyProp());
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $lazyProp = new LazyProp('date');

        $this->assertSame('date', $lazyProp());
    }

    public function test_is_ignored_on_first_load(): void
    {
        $this->assertInstanceOf(IgnoreFirstLoad::class, new LazyProp(fn () => 'value'));
    }
}
