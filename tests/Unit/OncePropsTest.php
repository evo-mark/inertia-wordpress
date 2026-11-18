<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Props\OnceProp;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * Once props are resolved once and then cached client-side; the client reports already-loaded
 * once props back via the X-Inertia-Except-Once-Props header.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseFactoryTest.php
 */
class OncePropsTest extends TestCase
{
    public function test_once_props_are_always_resolved_on_initial_page_load(): void
    {
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_resolved_on_initial_page_load_even_with_the_except_header(): void
    {
        // Only Inertia (XHR) visits are allowed to skip once props.
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')], [
            'X-Inertia-Except-Once-Props' => 'foo',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
    }

    public function test_fresh_once_props_are_included_on_initial_page_load(): void
    {
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')->fresh()]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_resolved_with_a_custom_key_and_ttl_value(): void
    {
        $now = $this->freezeTime();

        $page = $this->page('User/Edit', [
            'foo' => Inertia::once(fn () => 'bar')->as('baz')->until($now->copy()->addMinute()),
        ], ['X-Inertia' => 'true']);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame([
            'baz' => ['prop' => 'foo', 'expiresAt' => $now->copy()->addMinute()->getTimestampMs()],
        ], $page['onceProps']);
    }

    public function test_once_props_are_not_resolved_on_subsequent_requests_when_they_are_in_the_once_props_header(): void
    {
        $evaluated = false;

        $page = $this->page('User/Edit', [
            'foo' => Inertia::once(function () use (&$evaluated) {
                $evaluated = true;

                return 'bar';
            }),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Except-Once-Props' => 'foo',
        ]);

        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertFalse($evaluated);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_with_a_custom_key_are_matched_by_that_key(): void
    {
        $page = $this->page('User/Edit', [
            'foo' => Inertia::once(fn () => 'bar')->as('baz'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Except-Once-Props' => 'baz',
        ]);

        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertSame(['baz' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_resolved_on_subsequent_requests_when_the_once_props_header_is_missing(): void
    {
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')], ['X-Inertia' => 'true']);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_resolved_on_subsequent_requests_when_they_are_not_in_the_once_props_header(): void
    {
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')], [
            'X-Inertia' => 'true',
            'X-Inertia-Except-Once-Props' => 'baz',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_resolved_on_partial_requests_without_only_or_except(): void
    {
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'foo',
            'X-Inertia-Except-Once-Props' => 'foo',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_resolved_on_partial_requests_when_included_in_only_headers(): void
    {
        $page = $this->page('User/Edit', [
            'foo' => Inertia::once(fn () => 'bar'),
            'baz' => Inertia::once(fn () => 'qux'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'foo',
            'X-Inertia-Except-Once-Props' => 'foo',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertArrayNotHasKey('baz', $page['props']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_are_not_resolved_on_partial_requests_when_excluded_in_except_headers(): void
    {
        $page = $this->page('User/Edit', [
            'foo' => Inertia::once(fn () => 'bar'),
            'baz' => Inertia::once(fn () => 'qux'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Except' => 'foo',
            'X-Inertia-Except-Once-Props' => 'foo',
        ]);

        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertSame('qux', $page['props']['baz']);
        $this->assertSame(['baz' => ['prop' => 'baz', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_fresh_props_are_resolved_even_when_in_except_once_props_header(): void
    {
        $page = $this->page('User/Edit', ['foo' => Inertia::once(fn () => 'bar')->fresh()], [
            'X-Inertia' => 'true',
            'X-Inertia-Except-Once-Props' => 'foo',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_fresh_props_are_not_excluded_while_once_props_are_excluded(): void
    {
        $page = $this->page('User/Edit', [
            'foo' => Inertia::once(fn () => 'bar')->fresh(),
            'baz' => Inertia::once(fn () => 'qux'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Except-Once-Props' => 'foo,baz',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertArrayNotHasKey('baz', $page['props']);
        $this->assertSame([
            'foo' => ['prop' => 'foo', 'expiresAt' => null],
            'baz' => ['prop' => 'baz', 'expiresAt' => null],
        ], $page['onceProps']);
    }

    public function test_defer_props_that_are_once_and_already_loaded_are_excluded(): void
    {
        $page = $this->page('User/Edit', [
            'defer' => Inertia::defer(fn () => 'value')->once(),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Except-Once-Props' => 'defer',
        ]);

        $this->assertArrayNotHasKey('defer', $page['props']);
        $this->assertArrayNotHasKey('deferredProps', $page);
        $this->assertSame(['defer' => ['prop' => 'defer', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_defer_props_that_are_once_and_already_loaded_not_excluded_when_explicitly_requested(): void
    {
        $page = $this->page('User/Edit', [
            'defer' => Inertia::defer(fn () => 'value')->once(),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'defer',
            'X-Inertia-Except-Once-Props' => 'defer',
        ]);

        $this->assertSame('value', $page['props']['defer']);
        $this->assertArrayNotHasKey('deferredProps', $page);
        $this->assertSame(['defer' => ['prop' => 'defer', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_once_props_from_provides_inertia_properties_are_included_in_once_props_metadata(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            new ExamplePropsProvider([
                'foo' => Inertia::once(fn () => 'bar'),
            ]),
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame(['foo' => ['prop' => 'foo', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_share_once_shares_a_once_prop(): void
    {
        $request = $this->makeInertiaRequest('/');
        $prop = Inertia::shareOnce('settings', fn () => ['theme' => 'dark']);
        $request->setComponent('User/Edit');

        $page = $request->getPageData();

        $this->assertInstanceOf(OnceProp::class, $prop);
        $this->assertSame(['theme' => 'dark'], $page['props']['settings']);
        $this->assertSame(['settings' => ['prop' => 'settings', 'expiresAt' => null]], $page['onceProps']);
    }

    public function test_share_once_is_chainable(): void
    {
        $request = $this->makeInertiaRequest('/');
        Inertia::shareOnce('settings', fn () => ['theme' => 'dark'])
            ->as('app-settings')
            ->until(60);
        $request->setComponent('User/Edit');

        $page = $request->getPageData();

        $this->assertArrayHasKey('app-settings', $page['onceProps']);
        $this->assertSame('settings', $page['onceProps']['app-settings']['prop']);
        $this->assertNotNull($page['onceProps']['app-settings']['expiresAt']);
    }

    public function test_already_loaded_shared_once_props_are_skipped(): void
    {
        $request = $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Except-Once-Props' => 'settings']);
        Inertia::shareOnce('settings', fn () => ['theme' => 'dark']);
        $request->setComponent('User/Edit');

        $page = $request->getPageData();

        $this->assertArrayNotHasKey('settings', $page['props']);
        $this->assertSame(['settings' => ['prop' => 'settings', 'expiresAt' => null]], $page['onceProps']);
    }
}
