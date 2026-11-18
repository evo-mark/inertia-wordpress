<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use Brain\Monkey\Functions;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Props\LazyProp;
use EvoMark\InertiaWordpress\Props\OptionalProp;
use EvoMark\InertiaWordpress\Tests\Stubs\ArrayableValue;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 * @see https://inertiajs.com/the-protocol#partial-reloads
 */
class PartialReloadTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function partial(string $component, ?string $only = null, ?string $except = null): array
    {
        return array_filter([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => $component,
            'X-Inertia-Partial-Data' => $only,
            'X-Inertia-Partial-Except' => $except,
        ], fn ($value) => $value !== null);
    }

    public function test_xhr_partial_response(): void
    {
        $page = $this->page(
            'User/Edit',
            ['user' => (object) ['name' => 'Jonathan'], 'partial' => 'partial-data'],
            $this->partial('User/Edit', only: 'partial')
        );

        $this->assertSame('User/Edit', $page['component']);
        $this->assertSame(['partial' => 'partial-data'], $page['props']);
        $this->assertSame('/user/123', $page['url']);
        $this->assertSame('123', $page['version']);
    }

    public function test_exclude_props_from_partial_response(): void
    {
        $page = $this->page(
            'User/Edit',
            ['user' => (object) ['name' => 'Jonathan'], 'partial' => 'partial-data'],
            $this->partial('User/Edit', except: 'user')
        );

        $this->assertSame(['partial' => 'partial-data'], $page['props']);
    }

    public function test_partial_reloads_accept_multiple_comma_separated_keys(): void
    {
        $page = $this->page(
            'User/Edit',
            ['a' => 1, 'b' => 2, 'c' => 3],
            $this->partial('User/Edit', only: 'a,c')
        );

        $this->assertSame(['a' => 1, 'c' => 3], $page['props']);
    }

    public function test_excluded_closures_are_never_evaluated(): void
    {
        $evaluated = false;

        $page = $this->page('User/Index', [
            'users' => fn () => [['name' => 'Jonathan']],
            'organizations' => function () use (&$evaluated) {
                $evaluated = true;

                return [['name' => 'Inertia']];
            },
        ], $this->partial('User/Index', only: 'users'), '/users');

        $this->assertSame([['name' => 'Jonathan']], $page['props']['users']);
        $this->assertArrayNotHasKey('organizations', $page['props']);
        $this->assertFalse($evaluated, 'Props excluded from a partial reload must not be resolved.');
    }

    public function test_nested_partial_props(): void
    {
        $page = $this->page('User/Edit', [
            'auth' => [
                'user' => new LazyProp(fn () => [
                    'name' => 'Jonathan Reinink',
                    'email' => 'jonathan@example.com',
                ]),
                'refresh_token' => 'value',
                'token' => 'value',
            ],
            'shared' => [
                'flash' => 'value',
            ],
        ], $this->partial('User/Edit', only: 'auth.user,auth.refresh_token'));

        $this->assertArrayNotHasKey('shared', $page['props']);
        $this->assertArrayNotHasKey('token', $page['props']['auth']);
        $this->assertSame('Jonathan Reinink', $page['props']['auth']['user']['name']);
        $this->assertSame('jonathan@example.com', $page['props']['auth']['user']['email']);
        $this->assertSame('value', $page['props']['auth']['refresh_token']);
    }

    public function test_exclude_nested_props_from_partial_response(): void
    {
        $page = $this->page('User/Edit', [
            'auth' => [
                'user' => new LazyProp(fn () => [
                    'name' => 'Jonathan Reinink',
                    'email' => 'jonathan@example.com',
                ]),
                'refresh_token' => 'value',
            ],
            'shared' => [
                'flash' => 'value',
            ],
        ], $this->partial('User/Edit', only: 'auth', except: 'auth.user'));

        $this->assertArrayNotHasKey('user', $page['props']['auth']);
        $this->assertArrayNotHasKey('shared', $page['props']);
        $this->assertSame('value', $page['props']['auth']['refresh_token']);
    }

    public function test_lazy_props_are_not_included_by_default(): void
    {
        $page = $this->page('Users', [
            'users' => [],
            'lazy' => new LazyProp(fn () => 'A lazy value'),
        ], ['X-Inertia' => 'true'], '/users');

        $this->assertSame([], $page['props']['users']);
        $this->assertArrayNotHasKey('lazy', $page['props']);
    }

    public function test_lazy_props_are_included_in_partial_reload(): void
    {
        $page = $this->page('Users', [
            'users' => [],
            'lazy' => new LazyProp(fn () => 'A lazy value'),
        ], $this->partial('Users', only: 'lazy'), '/users');

        $this->assertArrayNotHasKey('users', $page['props']);
        $this->assertSame('A lazy value', $page['props']['lazy']);
    }

    public function test_optional_props_are_not_included_by_default(): void
    {
        $page = $this->page('Users', [
            'users' => [],
            'optional' => new OptionalProp(fn () => 'An optional value'),
        ], ['X-Inertia' => 'true'], '/users');

        $this->assertArrayNotHasKey('optional', $page['props']);
    }

    public function test_optional_props_are_included_in_partial_reload(): void
    {
        $page = $this->page('Users', [
            'users' => [],
            'optional' => new OptionalProp(fn () => 'An optional value'),
        ], $this->partial('Users', only: 'optional'), '/users');

        $this->assertArrayNotHasKey('users', $page['props']);
        $this->assertSame('An optional value', $page['props']['optional']);
    }

    public function test_optional_props_are_included_in_partial_except_reload(): void
    {
        $page = $this->page('Users', [
            'users' => [],
            'optional' => new OptionalProp(fn () => 'An optional value'),
        ], $this->partial('Users', except: 'users'), '/users');

        $this->assertArrayNotHasKey('users', $page['props']);
        $this->assertSame('An optional value', $page['props']['optional']);
    }

    public function test_defer_arrayable_props_are_resolved_in_partial_reload(): void
    {
        $page = $this->page('Users', [
            'users' => [],
            'defer' => new DeferProp(fn () => new ArrayableValue(['foo' => 'bar'])),
        ], $this->partial('Users', only: 'defer'), '/users');

        $this->assertArrayNotHasKey('users', $page['props']);
        $this->assertSame(['foo' => 'bar'], $page['props']['defer']);
    }

    public function test_always_props_are_included_on_partial_reload(): void
    {
        $page = $this->page('User/Edit', [
            'user' => new LazyProp(fn () => [
                'name' => 'Jonathan Reinink',
                'email' => 'jonathan@example.com',
            ]),
            'data' => [
                'name' => 'Taylor Otwell',
            ],
            'errors' => new AlwaysProp(fn () => [
                'name' => 'The email field is required.',
            ]),
        ], $this->partial('User/Edit', only: 'data'));

        $this->assertSame('The email field is required.', $page['props']['errors']['name']);
        $this->assertSame('Taylor Otwell', $page['props']['data']['name']);
        $this->assertArrayNotHasKey('user', $page['props']);
    }

    public function test_always_props_are_included_on_partial_except_reload(): void
    {
        $page = $this->page('User/Edit', [
            'data' => ['name' => 'Taylor Otwell'],
            'errors' => new AlwaysProp(fn () => ['name' => 'Required.']),
        ], $this->partial('User/Edit', except: 'errors'));

        $this->assertSame('Required.', $page['props']['errors']['name']);
    }

    public function test_partial_headers_for_a_different_component_are_ignored(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'partial' => 'partial-data',
            'lazy' => new LazyProp(fn () => 'A lazy value'),
        ], $this->partial('Some/Other/Component', only: 'partial'));

        $this->assertSame(['name' => 'Jonathan'], $page['props']['user']);
        $this->assertSame('partial-data', $page['props']['partial']);
        $this->assertArrayNotHasKey('lazy', $page['props']);
    }

    public function test_partial_reloads_match_a_component_with_a_page_template(): void
    {
        Functions\when('get_the_ID')->justReturn(42);
        Functions\when('get_page_template_slug')->justReturn('FullWidth.vue');
        $component = 'Page?template=' . base64_encode('FullWidth.vue');

        $page = $this->page('Page', [
            'title' => 'Hello',
            'lazy' => new LazyProp(fn () => 'A lazy value'),
        ], $this->partial($component, only: 'lazy'));

        $this->assertSame(['lazy' => 'A lazy value'], $page['props']);
    }

    public function test_deferred_props_from_provides_inertia_properties_can_be_loaded_via_partial_request(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            new ExamplePropsProvider([
                'foo' => new DeferProp(fn () => 'bar', 'default'),
            ]),
        ], $this->partial('User/Edit', only: 'foo'));

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertArrayNotHasKey('user', $page['props']);
    }
}
