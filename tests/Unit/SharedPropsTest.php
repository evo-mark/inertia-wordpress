<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Tests\Stubs\ArrayableValue;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseFactoryTest.php
 * @see https://inertiajs.com/shared-data
 */
class SharedPropsTest extends TestCase
{
    /**
     * @param array<array-key, mixed>|object $props
     * @return array<string, mixed>
     */
    private function render(string $component = 'User/Edit', array|object $props = []): array
    {
        $request = $this->currentRequest();
        $request->setComponent($component);
        $request->setProps($props);

        return $request->getPageData();
    }

    public function test_shared_data_can_be_shared_from_anywhere(): void
    {
        Inertia::share('foo', 'bar');

        $this->assertSame('bar', $this->render()['props']['foo']);
    }

    public function test_page_props_take_precedence_over_shared_props(): void
    {
        Inertia::share('foo', 'shared');

        $this->assertSame('page', $this->render('User/Edit', ['foo' => 'page'])['props']['foo']);
    }

    public function test_shared_closures_are_resolved(): void
    {
        Inertia::share('query', fn () => ['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $this->render()['props']['query']);
    }

    public function test_dot_props_are_merged_from_shared(): void
    {
        Inertia::share('auth.user', [
            'name' => 'Jonathan',
        ]);

        $page = $this->render('User/Edit', [
            'auth.user.can.create_group' => false,
        ]);

        $this->assertSame([
            'auth' => [
                'user' => [
                    'name' => 'Jonathan',
                    'can' => [
                        'create_group' => false,
                    ],
                ],
            ],
        ], $page['props']);
    }

    public function test_dot_props_with_callbacks_are_merged_from_shared(): void
    {
        Inertia::share('auth.user', fn () => [
            'name' => 'Jonathan',
        ]);

        $page = $this->render('User/Edit', [
            'auth.user.can.create_group' => false,
        ]);

        $this->assertSame([
            'auth' => [
                'user' => [
                    'name' => 'Jonathan',
                    'can' => [
                        'create_group' => false,
                    ],
                ],
            ],
        ], $page['props']);
    }

    public function test_can_get_shared_data(): void
    {
        Inertia::share('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], Inertia::getShared());
        $this->assertSame('bar', Inertia::getShared('foo'));
        $this->assertSame('default', Inertia::getShared('missing', 'default'));
    }

    public function test_can_flush_shared_data(): void
    {
        Inertia::share('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], Inertia::getShared());

        Inertia::flushShared();
        $this->assertSame([], Inertia::getShared());
    }

    public function test_can_share_an_array_of_props(): void
    {
        Inertia::share(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], Inertia::getShared());
    }

    public function test_sharing_an_array_merges_with_existing_shared_props(): void
    {
        Inertia::share('first', 1);
        Inertia::share(['second' => 2]);

        $this->assertSame(['first' => 1, 'second' => 2], Inertia::getShared());
    }

    public function test_can_share_an_arrayable(): void
    {
        Inertia::share(new ArrayableValue(['foo' => 'bar']));

        $this->assertSame('bar', $this->render()['props']['foo']);
    }

    public function test_can_share_instances_of_provides_inertia_props(): void
    {
        Inertia::share(new ExamplePropsProvider([
            'shared' => 'data',
        ]));

        $page = $this->render('User/Edit', ['regular' => 'prop']);

        $this->assertSame('data', $page['props']['shared']);
        $this->assertSame('prop', $page['props']['regular']);
    }

    public function test_can_share_arrays_containing_provides_inertia_props(): void
    {
        Inertia::share([
            'regular' => 'shared_prop',
            new ExamplePropsProvider([
                'from_object' => 'shared_value',
            ]),
        ]);

        $page = $this->render('User/Edit', ['component' => 'prop']);

        $this->assertSame('shared_prop', $page['props']['regular']);
        $this->assertSame('shared_value', $page['props']['from_object']);
        $this->assertSame('prop', $page['props']['component']);
    }

    public function test_shared_props_respect_partial_reloads(): void
    {
        $this->makeInertiaRequest('/', 'GET', [
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'regular',
        ]);
        Inertia::share('shared', 'value');

        $page = $this->render('User/Edit', ['regular' => 'prop']);

        $this->assertSame(['regular' => 'prop'], $page['props']);
    }
}
