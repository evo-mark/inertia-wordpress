<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Contracts\Deferrable;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/DeferPropTest.php
 */
class DeferPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $deferProp = new DeferProp(fn () => 'A deferred value', 'default');

        $this->assertSame('A deferred value', $deferProp());
        $this->assertSame('default', $deferProp->group());
    }

    public function test_group_defaults_to_default(): void
    {
        $deferProp = new DeferProp(fn () => 'A deferred value');

        $this->assertSame('default', $deferProp->group());
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $deferProp = new DeferProp('date');

        $this->assertSame('date', $deferProp());
    }

    public function test_is_deferred_and_ignored_on_first_load(): void
    {
        $deferProp = new DeferProp(fn () => 'value');

        $this->assertInstanceOf(IgnoreFirstLoad::class, $deferProp);
        $this->assertInstanceOf(Deferrable::class, $deferProp);
        $this->assertTrue($deferProp->shouldDefer());
    }

    public function test_does_not_merge_by_default(): void
    {
        $deferProp = new DeferProp(fn () => 'value');

        $this->assertInstanceOf(Mergeable::class, $deferProp);
        $this->assertFalse($deferProp->shouldMerge());
        $this->assertFalse($deferProp->shouldDeepMerge());
    }

    public function test_can_invoke_and_merge(): void
    {
        $deferProp = (new DeferProp(fn () => 'A deferred value'))->merge();

        $this->assertSame('A deferred value', $deferProp());
        $this->assertTrue($deferProp->shouldMerge());
        $this->assertFalse($deferProp->shouldDeepMerge());
    }

    public function test_can_invoke_and_deep_merge(): void
    {
        $deferProp = (new DeferProp(fn () => 'A deferred value'))->deepMerge();

        $this->assertSame('A deferred value', $deferProp());
        $this->assertTrue($deferProp->shouldMerge());
        $this->assertTrue($deferProp->shouldDeepMerge());
    }

    public function test_is_onceable(): void
    {
        $deferProp = (new DeferProp(fn () => 'value'))
            ->once(as: 'custom-key', until: 60);

        $this->assertInstanceOf(Onceable::class, $deferProp);
        $this->assertTrue($deferProp->shouldResolveOnce());
        $this->assertSame('custom-key', $deferProp->getKey());
        $this->assertNotNull($deferProp->expiresAt());
    }
}
