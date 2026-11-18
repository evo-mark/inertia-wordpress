<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Props\OptionalProp;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/OptionalPropTest.php
 */
class OptionalPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $optionalProp = new OptionalProp(fn () => 'An optional value');

        $this->assertSame('An optional value', $optionalProp());
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $optionalProp = new OptionalProp('date');

        $this->assertSame('date', $optionalProp());
    }

    public function test_is_ignored_on_first_load(): void
    {
        $this->assertInstanceOf(IgnoreFirstLoad::class, new OptionalProp(fn () => 'value'));
    }

    public function test_is_onceable(): void
    {
        $optionalProp = (new OptionalProp(fn () => 'value'))
            ->once()
            ->as('custom-key')
            ->until(60);

        $this->assertInstanceOf(Onceable::class, $optionalProp);
        $this->assertTrue($optionalProp->shouldResolveOnce());
        $this->assertSame('custom-key', $optionalProp->getKey());
        $this->assertNotNull($optionalProp->expiresAt());
    }
}
