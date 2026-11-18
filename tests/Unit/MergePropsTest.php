<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * Merge metadata on the page object: mergeProps, prependProps, deepMergeProps, matchPropsOn.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 * @see https://inertiajs.com/merging-props
 */
class MergePropsTest extends TestCase
{
    public function test_server_response_with_merge_props(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => new MergeProp('foo value'),
            'bar' => new MergeProp('bar value'),
        ]);

        $this->assertSame('foo value', $page['props']['foo']);
        $this->assertSame('bar value', $page['props']['bar']);
        $this->assertSame(['foo', 'bar'], $page['mergeProps']);
        $this->assertArrayNotHasKey('prependProps', $page);
        $this->assertArrayNotHasKey('deepMergeProps', $page);
        $this->assertArrayNotHasKey('matchPropsOn', $page);
    }

    public function test_server_response_with_merge_props_that_should_prepend(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new MergeProp('foo value'))->prepend(),
            'bar' => new MergeProp('bar value'),
        ]);

        $this->assertSame(['bar'], $page['mergeProps']);
        $this->assertSame(['foo'], $page['prependProps']);
    }

    public function test_server_response_with_merge_props_that_has_nested_paths_to_append_and_prepend(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new MergeProp(['data' => [['id' => 1], ['id' => 2]]]))->append('data'),
            'bar' => (new MergeProp(['data' => ['items' => [['uuid' => 1], ['uuid' => 2]]]]))->prepend('data.items'),
        ]);

        $this->assertSame(['data' => [['id' => 1], ['id' => 2]]], $page['props']['foo']);
        $this->assertSame(['data' => ['items' => [['uuid' => 1], ['uuid' => 2]]]], $page['props']['bar']);
        $this->assertSame(['foo.data'], $page['mergeProps']);
        $this->assertSame(['bar.data.items'], $page['prependProps']);
        $this->assertArrayNotHasKey('matchPropsOn', $page);
    }

    public function test_server_response_with_merge_props_that_has_nested_paths_to_append_and_prepend_with_match_on_strategies(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new MergeProp(['data' => [['id' => 1], ['id' => 2]]]))->append('data', 'id'),
            'bar' => (new MergeProp(['data' => ['items' => [['uuid' => 1], ['uuid' => 2]]]]))->prepend('data.items', 'uuid'),
        ]);

        $this->assertSame(['foo.data'], $page['mergeProps']);
        $this->assertSame(['bar.data.items'], $page['prependProps']);
        $this->assertSame(['foo.data.id', 'bar.data.items.uuid'], $page['matchPropsOn']);
    }

    public function test_server_response_with_deep_merge_props(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new MergeProp('foo value'))->deepMerge(),
            'bar' => (new MergeProp('bar value'))->deepMerge(),
        ]);

        $this->assertSame(['foo', 'bar'], $page['deepMergeProps']);
        $this->assertArrayNotHasKey('mergeProps', $page);
    }

    public function test_server_response_with_match_on_props(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new MergeProp('foo value'))->matchOn('foo-key')->deepMerge(),
            'bar' => (new MergeProp('bar value'))->matchOn('bar-key')->deepMerge(),
        ]);

        $this->assertSame(['foo', 'bar'], $page['deepMergeProps']);
        $this->assertSame(['foo.foo-key', 'bar.bar-key'], $page['matchPropsOn']);
    }

    public function test_server_response_with_defer_and_merge_props(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new DeferProp(fn () => 'foo value', 'default'))->merge(),
            'bar' => new MergeProp('bar value'),
        ]);

        $this->assertSame(['user' => ['name' => 'Jonathan'], 'bar' => 'bar value'], $page['props']);
        $this->assertSame(['default' => ['foo']], $page['deferredProps']);
        $this->assertSame(['foo', 'bar'], $page['mergeProps']);
    }

    public function test_server_response_with_defer_and_deep_merge_props(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => (new DeferProp(fn () => 'foo value', 'default'))->deepMerge(),
            'bar' => (new MergeProp('bar value'))->deepMerge(),
        ]);

        $this->assertSame(['default' => ['foo']], $page['deferredProps']);
        $this->assertSame(['foo', 'bar'], $page['deepMergeProps']);
    }

    public function test_metadata_keys_follow_the_protocol_order(): void
    {
        $page = $this->page('User/Edit', [
            'a' => (new MergeProp([]))->append('data', 'id'),
            'b' => (new MergeProp([]))->prepend(),
            'c' => (new MergeProp([]))->deepMerge(),
            'd' => (new DeferProp(fn () => []))->merge(),
        ]);

        $this->assertSame(
            ['component', 'props', 'url', 'version', 'clearHistory', 'encryptHistory', 'mergeProps', 'prependProps', 'deepMergeProps', 'matchPropsOn', 'deferredProps'],
            array_keys($page)
        );
    }

    public function test_exclude_merge_props_from_partial_only_response(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => new MergeProp('foo value'),
            'bar' => new MergeProp('bar value'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'user',
        ]);

        $this->assertSame('Jonathan', $page['props']['user']['name']);
        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertArrayNotHasKey('bar', $page['props']);
        $this->assertArrayNotHasKey('mergeProps', $page);
    }

    public function test_exclude_merge_props_from_partial_except_response(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => new MergeProp('foo value'),
            'bar' => new MergeProp('bar value'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Except' => 'foo',
        ]);

        $this->assertSame('Jonathan', $page['props']['user']['name']);
        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertArrayHasKey('bar', $page['props']);
        $this->assertSame(['bar'], $page['mergeProps']);
    }

    public function test_exclude_merge_props_when_passed_in_reset_header(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            'foo' => new MergeProp('foo value'),
            'bar' => new MergeProp('bar value'),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'foo',
            'X-Inertia-Reset' => 'foo',
        ]);

        $this->assertSame('foo value', $page['props']['foo']);
        $this->assertArrayNotHasKey('bar', $page['props']);
        $this->assertArrayNotHasKey('mergeProps', $page);
    }

    public function test_shared_merge_props_are_included_in_merge_metadata(): void
    {
        $request = $this->makeRequest('/user/123');
        $request->share('notifications', new MergeProp(['a']));
        $request->setComponent('User/Edit');
        $request->setProps(['posts' => new MergeProp(['b'])]);

        $this->assertSame(['notifications', 'posts'], $request->getPageData()['mergeProps']);
    }

    public function test_merge_props_from_provides_inertia_properties_are_included_in_merge_props_metadata(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            new ExamplePropsProvider([
                'foo' => new MergeProp('foo value'),
            ]),
        ]);

        $this->assertSame('foo value', $page['props']['foo']);
        $this->assertSame(['foo'], $page['mergeProps']);
    }

    public function test_deferred_merge_props_from_provides_inertia_properties_include_both_metadata(): void
    {
        $page = $this->page('User/Edit', [
            'user' => ['name' => 'Jonathan'],
            new ExamplePropsProvider([
                'foo' => (new DeferProp(fn () => 'foo value', 'default'))->merge(),
            ]),
        ]);

        $this->assertArrayNotHasKey('foo', $page['props']);
        $this->assertSame(['default' => ['foo']], $page['deferredProps']);
        $this->assertSame(['foo'], $page['mergeProps']);
    }
}
