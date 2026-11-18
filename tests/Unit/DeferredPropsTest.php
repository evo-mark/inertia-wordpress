<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 * @see https://inertiajs.com/deferred-props
 */
class DeferredPropsTest extends TestCase
{
    public function test_server_response_with_deferred_prop(): void
    {
        $evaluated = false;

        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => new DeferProp(function () use (&$evaluated) {
                $evaluated = true;

                return 'bar';
            }, 'default'),
        ]);

        $this->assertSame(['user' => ['name' => 'Jonathan']], $page['props']);
        $this->assertSame(['default' => ['foo']], $page['deferredProps']);
        $this->assertFalse($evaluated, 'Deferred props must not be resolved on the initial visit.');
    }

    public function test_server_response_with_deferred_prop_and_multiple_groups(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => new DeferProp(fn () => 'foo value', 'default'),
            'bar' => new DeferProp(fn () => 'bar value', 'default'),
            'baz' => new DeferProp(fn () => 'baz value', 'custom'),
        ]);

        $this->assertSame(['user' => ['name' => 'Jonathan']], $page['props']);
        $this->assertSame([
            'default' => ['foo', 'bar'],
            'custom' => ['baz'],
        ], $page['deferredProps']);
    }

    public function test_xhr_response_with_deferred_props_includes_deferred_metadata(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'results' => new DeferProp(fn () => ['data' => ['item1', 'item2']], 'default'),
        ], ['X-Inertia' => 'true']);

        $this->assertSame('Jonathan', $page['props']['user']['name']);
        $this->assertArrayNotHasKey('results', $page['props']);
        $this->assertSame(['default' => ['results']], $page['deferredProps']);
    }

    public function test_deferred_metadata_is_omitted_from_partial_reloads(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'results' => new DeferProp(fn () => ['item1', 'item2'], 'default'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'results',
        ]);

        $this->assertSame(['results' => ['item1', 'item2']], $page['props']);
        $this->assertArrayNotHasKey('deferredProps', $page);
    }

    public function test_shared_deferred_props_are_included_in_deferred_metadata(): void
    {
        $request = $this->makeRequest('/user/123');
        $request->share('notifications', new DeferProp(fn () => [], 'sidebar'));
        $request->setComponent('User/Edit');
        $request->setProps(['user' => ['name' => 'Jonathan']]);

        $page = $request->getPageData();

        $this->assertArrayNotHasKey('notifications', $page['props']);
        $this->assertSame(['sidebar' => ['notifications']], $page['deferredProps']);
    }

    public function test_deferred_props_from_provides_inertia_properties_are_included_in_deferred_props_metadata(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            new ExamplePropsProvider([
                'foo' => new DeferProp(fn () => 'bar', 'default'),
            ]),
        ]);

        $this->assertSame('Jonathan', $page['props']['user']['name']);
        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertSame(['default' => ['foo']], $page['deferredProps']);
    }

    public function test_deferred_props_from_provides_inertia_properties_with_multiple_groups(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            new ExamplePropsProvider([
                'foo' => new DeferProp(fn () => 'foo value', 'default'),
                'bar' => new DeferProp(fn () => 'bar value', 'custom'),
            ]),
        ]);

        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertArrayNotHasKey('bar', $page['props']);
        $this->assertSame([
            'default' => ['foo'],
            'custom' => ['bar'],
        ], $page['deferredProps']);
    }
}
