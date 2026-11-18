<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Tests\Stubs\InvokableValue;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/AlwaysPropTest.php
 */
class AlwaysPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $alwaysProp = new AlwaysProp(fn () => 'An always value');

        $this->assertSame('An always value', $alwaysProp());
    }

    public function test_can_accept_scalar_values(): void
    {
        $alwaysProp = new AlwaysProp('An always value');

        $this->assertSame('An always value', $alwaysProp());
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $alwaysProp = new AlwaysProp('date');

        $this->assertSame('date', $alwaysProp());
    }

    public function test_arrays_matching_callable_syntax_are_not_invoked(): void
    {
        $alwaysProp = new AlwaysProp([\DateTime::class, 'createFromFormat']);

        $this->assertSame([\DateTime::class, 'createFromFormat'], $alwaysProp());
    }

    public function test_can_accept_invokable_objects(): void
    {
        $alwaysProp = new AlwaysProp(new InvokableValue('An always value'));

        $this->assertSame('An always value', $alwaysProp());
    }
}
