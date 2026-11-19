<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use Brain\Monkey\Functions;
use EvoMark\InertiaWordpress\Data\RenderContext;
use EvoMark\InertiaWordpress\Contracts\ProvidesInertiaProperties;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Request\RequestHandler;
use EvoMark\InertiaWordpress\Tests\Stubs\ArrayableValue;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\TestCase;
use GuzzleHttp\Promise\FulfilledPromise;

/**
 * The shape of the page object and how plain props are resolved into it.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 * @see https://inertiajs.com/the-protocol#the-page-object
 */
class PageObjectTest extends TestCase
{
    public function test_server_response(): void
    {
        $page = $this->page('User/Edit', ['user' => ['name' => 'Jonathan']]);

        $this->assertSame('User/Edit', $page['component']);
        $this->assertSame('Jonathan', $page['props']['user']['name']);
        $this->assertSame('/user/123', $page['url']);
        $this->assertSame('123', $page['version']);
        $this->assertFalse($page['clearHistory']);
        $this->assertFalse($page['encryptHistory']);
    }

    public function test_page_object_keys_follow_the_protocol_order(): void
    {
        $page = $this->page('User/Edit', ['user' => ['name' => 'Jonathan']]);

        $this->assertSame(
            ['component', 'props', 'url', 'version', 'clearHistory', 'encryptHistory'],
            array_keys($page)
        );
    }

    public function test_optional_metadata_keys_are_omitted_when_unused(): void
    {
        $page = $this->page('User/Edit', ['user' => ['name' => 'Jonathan']]);

        foreach (['mergeProps', 'prependProps', 'deepMergeProps', 'matchPropsOn', 'deferredProps', 'scrollProps', 'onceProps', 'flash'] as $key) {
            $this->assertArrayNotHasKey($key, $page);
        }
    }

    public function test_xhr_response(): void
    {
        $page = $this->page('User/Edit', ['user' => (object) ['name' => 'Jonathan']], ['X-Inertia' => 'true']);

        $this->assertSame('User/Edit', $page['component']);
        $this->assertSame('Jonathan', $page['props']['user']->name);
        $this->assertSame('/user/123', $page['url']);
        $this->assertSame('123', $page['version']);
    }

    public function test_the_version_is_cast_to_a_string(): void
    {
        $page = $this->page('User/Edit', [], [], '/user/123', 1597347897973);

        $this->assertSame('1597347897973', $page['version']);
    }

    public function test_the_version_defaults_to_an_empty_string(): void
    {
        $request = $this->makeRequest('/user/123');
        $request->setComponent('User/Edit');

        $this->assertSame('', $request->getPageData()['version']);
    }

    public function test_the_component_carries_the_selected_page_template(): void
    {
        Functions\when('get_the_ID')->justReturn(42);
        Functions\when('get_page_template_slug')->justReturn('FullWidth.vue');

        $page = $this->page('Page');

        $this->assertSame('Page?template=' . base64_encode('FullWidth.vue'), $page['component']);
    }

    public function test_closure_props_are_resolved(): void
    {
        $page = $this->page('User/Index', [
            'users' => fn () => [['name' => 'Jonathan']],
            'organizations' => fn () => [['name' => 'Inertia']],
        ], ['X-Inertia' => 'true'], '/users');

        $this->assertSame([['name' => 'Jonathan']], $page['props']['users']);
        $this->assertSame([['name' => 'Inertia']], $page['props']['organizations']);
    }

    public function test_nested_closure_props_are_resolved(): void
    {
        $page = $this->page('User/Index', [
            'something' => fn () => [
                'users' => fn () => [['name' => 'Jonathan']],
                'resource' => new ArrayableValue(['name' => 'Taylor']),
            ],
        ], ['X-Inertia' => 'true'], '/users');

        $this->assertSame([['name' => 'Jonathan']], $page['props']['something']['users']);
        $this->assertSame(['name' => 'Taylor'], $page['props']['something']['resource']);
    }

    public function test_arrayable_prop_response(): void
    {
        $page = $this->page('User/Edit', ['user' => new ArrayableValue(['name' => 'Jonathan'])], ['X-Inertia' => 'true']);

        $this->assertSame(['name' => 'Jonathan'], $page['props']['user']);
    }

    public function test_promise_props_are_resolved(): void
    {
        $user = (object) ['name' => 'Jonathan'];

        $page = $this->page('User/Edit', ['user' => new FulfilledPromise($user)], ['X-Inertia' => 'true']);

        $this->assertSame($user, $page['props']['user']);
    }

    public function test_string_function_names_are_not_invoked_as_callables(): void
    {
        $page = $this->page('User/Edit', [
            'plain' => 'date',
            'always' => new AlwaysProp('date'),
            'merge' => new MergeProp('trim'),
        ]);

        $this->assertSame('date', $page['props']['plain']);
        $this->assertSame('date', $page['props']['always']);
        $this->assertSame('trim', $page['props']['merge']);
    }

    public function test_arrays_matching_callable_syntax_are_not_invoked(): void
    {
        $page = $this->page('User/Edit', [
            'job' => [
                'name' => 'Import',
                'fields' => [\DateTime::class, 'createFromFormat'],
            ],
        ]);

        $this->assertSame([\DateTime::class, 'createFromFormat'], $page['props']['job']['fields']);
    }

    public function test_top_level_dot_props_get_unpacked(): void
    {
        $page = $this->page('User/Edit', [
            'auth' => [
                'user' => [
                    'name' => 'Jonathan Reinink',
                ],
            ],
            'auth.user.can' => [
                'do.stuff' => true,
            ],
            'product' => ['name' => 'My example product'],
        ], ['X-Inertia' => 'true'], '/products/123');

        $user = $page['props']['auth']['user'];

        $this->assertSame('Jonathan Reinink', $user['name']);
        $this->assertTrue($user['can']['do.stuff']);
        $this->assertArrayNotHasKey('auth.user.can', $page['props']);
    }

    public function test_nested_dot_props_do_not_get_unpacked(): void
    {
        $page = $this->page('User/Edit', [
            'auth' => [
                'user.can' => [
                    'do.stuff' => true,
                ],
                'user' => [
                    'name' => 'Jonathan Reinink',
                ],
            ],
            'product' => ['name' => 'My example product'],
        ], ['X-Inertia' => 'true'], '/products/123');

        $auth = $page['props']['auth'];

        $this->assertSame('Jonathan Reinink', $auth['user']['name']);
        $this->assertTrue($auth['user.can']['do.stuff']);
        $this->assertArrayNotHasKey('can', $auth);
    }

    public function test_props_can_be_an_arrayable(): void
    {
        $page = $this->page('User/Edit', new ArrayableValue(['foo' => 'bar']), ['X-Inertia' => 'true']);

        $this->assertSame('bar', $page['props']['foo']);
    }

    public function test_props_can_be_a_provides_inertia_properties_instance(): void
    {
        $page = $this->page('User/Edit', new ExamplePropsProvider(['foo' => 'bar']), ['X-Inertia' => 'true']);

        $this->assertSame('bar', $page['props']['foo']);
    }

    public function test_props_can_contain_provides_inertia_properties_instances(): void
    {
        $page = $this->page('User/Edit', [
            'foo' => 'bar',
            new ExamplePropsProvider(collect(['baz' => 'qux'])),
            'quux' => 'corge',
        ]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertSame('qux', $page['props']['baz']);
        $this->assertSame('corge', $page['props']['quux']);
        $this->assertArrayNotHasKey(0, $page['props']);
    }

    public function test_provides_inertia_properties_is_called_once_per_page(): void
    {
        $provider = new class () implements ProvidesInertiaProperties {
            public int $calls = 0;

            public function toInertiaProperties(RenderContext $context): iterable
            {
                $this->calls++;

                return ['items' => new MergeProp(['a'])];
            }
        };

        $page = $this->page('User/Edit', [$provider]);

        $this->assertSame(['a'], $page['props']['items']);
        $this->assertSame(['items'], $page['mergeProps']);
        $this->assertSame(1, $provider->calls, 'Props, merge and deferred metadata should share one expansion.');
    }

    public function test_keys_after_a_provider_override_its_props(): void
    {
        $page = $this->page('User/Edit', [
            'before' => 'page',
            new ExamplePropsProvider(['before' => 'provider', 'after' => 'provider']),
            'after' => 'page',
        ]);

        $this->assertSame('provider', $page['props']['before']);
        $this->assertSame('page', $page['props']['after']);
    }

    public function test_provides_inertia_properties_receives_the_render_context(): void
    {
        $provider = new class () implements ProvidesInertiaProperties {
            public ?RenderContext $context = null;

            public function toInertiaProperties(RenderContext $context): iterable
            {
                $this->context = $context;

                return ['foo' => 'bar'];
            }
        };

        $page = $this->page('User/Edit', [$provider]);

        $this->assertSame('bar', $page['props']['foo']);
        $this->assertNotNull($provider->context);
        $this->assertSame('User/Edit', $provider->context->component);
        $this->assertInstanceOf(RequestHandler::class, $provider->context->request);
    }

    public function test_trailing_slashes_in_a_url_are_preserved(): void
    {
        $page = $this->page('User/Index', [], ['X-Inertia' => 'true'], '/users/');

        $this->assertSame('/users/', $page['url']);
    }

    public function test_trailing_slashes_in_a_url_with_query_parameters_are_preserved(): void
    {
        $page = $this->page('User/Index', [], ['X-Inertia' => 'true'], '/users/?page=1&sort=name');

        $this->assertSame('/users/?page=1&sort=name', $page['url']);
    }

    public function test_a_url_without_trailing_slash_is_resolved_correctly(): void
    {
        $page = $this->page('User/Index', [], ['X-Inertia' => 'true'], '/users');

        $this->assertSame('/users', $page['url']);
    }

    public function test_a_url_without_trailing_slash_and_query_parameters_is_resolved_correctly(): void
    {
        $page = $this->page('User/Index', [], ['X-Inertia' => 'true'], '/users?page=1&sort=name');

        $this->assertSame('/users?page=1&sort=name', $page['url']);
    }

    public function test_the_page_url_doesnt_double_up_in_a_subdirectory_install(): void
    {
        Functions\when('home_url')->alias(
            fn ($path = '') => 'http://example.test/subpath' . ($path === '' ? '' : '/' . ltrim($path, '/'))
        );

        $page = $this->page('Product/Show', [], ['X-Inertia' => 'true'], '/subpath/product/123');

        $this->assertSame('/subpath/product/123', $page['url']);
    }
}
