<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use Carbon\CarbonInterval;
use DateInterval;
use DateTimeImmutable;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Props\OnceProp;
use EvoMark\InertiaWordpress\Tests\Stubs\TestBackedEnum;
use EvoMark\InertiaWordpress\Tests\Stubs\TestUnitEnum;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/OncePropTest.php
 */
class OncePropTest extends TestCase
{
    public function test_can_invoke_with_a_callback(): void
    {
        $onceProp = new OnceProp(fn () => 'A once prop value');

        $this->assertSame('A once prop value', $onceProp());
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $onceProp = new OnceProp('date');

        $this->assertSame('date', $onceProp());
    }

    public function test_resolves_once_and_is_included_on_first_load(): void
    {
        $onceProp = new OnceProp(fn () => 'value');

        $this->assertInstanceOf(Onceable::class, $onceProp);
        $this->assertNotInstanceOf(IgnoreFirstLoad::class, $onceProp);
        $this->assertTrue($onceProp->shouldResolveOnce());
    }

    public function test_has_no_custom_key_or_expiry_by_default(): void
    {
        $onceProp = new OnceProp(fn () => 'value');

        $this->assertNull($onceProp->getKey());
        $this->assertNull($onceProp->expiresAt());
    }

    public function test_can_set_custom_key(): void
    {
        $onceProp = new OnceProp(fn () => 'value');

        $result = $onceProp->as('custom-key');
        $this->assertSame($onceProp, $result);
        $this->assertSame('custom-key', $onceProp->getKey());

        $onceProp->as(TestBackedEnum::Foo);
        $this->assertSame('foo-value', $onceProp->getKey());

        $onceProp->as(TestUnitEnum::Baz);
        $this->assertSame('Baz', $onceProp->getKey());
    }

    public function test_expiry_can_be_set_in_seconds(): void
    {
        $now = $this->freezeTime();

        $onceProp = (new OnceProp(fn () => 'value'))->until(60);

        $this->assertSame($now->copy()->addSeconds(60)->getTimestampMs(), $onceProp->expiresAt());
    }

    public function test_expiry_can_be_set_with_a_date_interval(): void
    {
        $now = $this->freezeTime();

        $this->assertSame(
            $now->copy()->addMinutes(5)->getTimestampMs(),
            (new OnceProp(fn () => 'value'))->until(new DateInterval('PT5M'))->expiresAt()
        );

        $this->assertSame(
            $now->copy()->addHour()->getTimestampMs(),
            (new OnceProp(fn () => 'value'))->until(CarbonInterval::hour())->expiresAt()
        );
    }

    public function test_expiry_can_be_set_with_a_date_time(): void
    {
        $this->freezeTime();
        $expires = new DateTimeImmutable('2026-01-02 00:00:00');

        $onceProp = (new OnceProp(fn () => 'value'))->until($expires);

        $this->assertSame($expires->getTimestamp() * 1000, $onceProp->expiresAt());
    }

    public function test_should_not_be_refreshed_by_default(): void
    {
        $onceProp = new OnceProp(fn () => 'value');

        $this->assertFalse($onceProp->shouldBeRefreshed());
    }

    public function test_can_forcefully_refresh(): void
    {
        $onceProp = new OnceProp(fn () => 'value');
        $onceProp->fresh();

        $this->assertTrue($onceProp->shouldBeRefreshed());
    }

    public function test_can_disable_forceful_refresh(): void
    {
        $onceProp = new OnceProp(fn () => 'value');
        $onceProp->fresh();
        $onceProp->fresh(false);

        $this->assertFalse($onceProp->shouldBeRefreshed());
    }
}
