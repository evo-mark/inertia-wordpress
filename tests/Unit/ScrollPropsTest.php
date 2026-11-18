<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Props\ScrollProp;
use EvoMark\InertiaWordpress\Tests\Stubs\StaticScrollMetadata;
use EvoMark\InertiaWordpress\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * scrollProps metadata on the page object, used by the client's <InfiniteScroll> component.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ScrollPropTest.php
 */
class ScrollPropsTest extends TestCase
{
    /**
     * @return array<int, array{id: int}>
     */
    private function users(int $count = 15): array
    {
        return array_map(fn ($id) => ['id' => $id], range(1, $count));
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public static function resetUsersProp(): array
    {
        return [
            'no reset' => [false],
            'with reset' => [true],
        ];
    }

    #[DataProvider('resetUsersProp')]
    public function test_server_response_with_scroll_props(bool $resetUsersProp): void
    {
        $headers = $resetUsersProp ? ['X-Inertia-Reset' => 'users'] : [];

        $page = $this->page('User/Index', [
            'users' => new ScrollProp(['data' => [['id' => 1]]], 'data', new StaticScrollMetadata('paged', null, 2, 1)),
        ], $headers);

        $this->assertSame(['data' => [['id' => 1]]], $page['props']['users']);
        $this->assertSame([
            'users' => [
                'pageName' => 'paged',
                'previousPage' => null,
                'nextPage' => 2,
                'currentPage' => 1,
                'reset' => $resetUsersProp,
            ],
        ], $page['scrollProps']);
    }

    public function test_scroll_props_append_at_the_wrapper_path_by_default(): void
    {
        $page = $this->page('User/Index', [
            'users' => Inertia::scroll(['data' => $this->users()], 'data', new StaticScrollMetadata()),
        ], ['X-Inertia' => 'true']);

        $this->assertSame(['users.data'], $page['mergeProps']);
        $this->assertArrayNotHasKey('prependProps', $page);
    }

    public function test_scroll_props_prepend_when_the_merge_intent_header_asks_for_it(): void
    {
        $page = $this->page('User/Index', [
            'users' => Inertia::scroll(['data' => $this->users()], 'data', new StaticScrollMetadata()),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Index',
            'X-Inertia-Partial-Data' => 'users',
            'X-Inertia-Infinite-Scroll-Merge-Intent' => 'prepend',
        ]);

        $this->assertSame(['users.data'], $page['prependProps']);
        $this->assertArrayNotHasKey('mergeProps', $page);
    }

    public function test_scroll_props_are_not_merged_when_reset(): void
    {
        $page = $this->page('User/Index', [
            'users' => Inertia::scroll(['data' => $this->users()], 'data', new StaticScrollMetadata()),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'User/Index',
            'X-Inertia-Partial-Data' => 'users',
            'X-Inertia-Reset' => 'users',
        ]);

        $this->assertArrayNotHasKey('mergeProps', $page);
        $this->assertTrue($page['scrollProps']['users']['reset']);
    }

    public function test_deferred_scroll_prop_is_excluded_from_initial_request(): void
    {
        $page = $this->page('Users/Index', [
            'users' => (new ScrollProp(fn () => ['data' => $this->users()], 'data', new StaticScrollMetadata()))->defer(),
        ], [], '/users');

        $this->assertArrayNotHasKey('users', $page['props']);
        $this->assertSame(['default' => ['users']], $page['deferredProps']);
        $this->assertArrayNotHasKey('scrollProps', $page);
    }

    public function test_deferred_scroll_prop_is_resolved_on_partial_request(): void
    {
        $page = $this->page('Users/Index', [
            'users' => (new ScrollProp(fn () => ['data' => $this->users()], 'data', new StaticScrollMetadata()))->defer(),
        ], [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'Users/Index',
            'X-Inertia-Partial-Data' => 'users',
        ], '/users');

        $this->assertCount(15, $page['props']['users']['data']);
        $this->assertSame('paged', $page['scrollProps']['users']['pageName']);
        $this->assertContains('users.data', $page['mergeProps']);
    }

    public function test_deferred_scroll_prop_can_have_custom_group(): void
    {
        $page = $this->page('Users/Index', [
            'users' => (new ScrollProp(fn () => ['data' => $this->users()], 'data', new StaticScrollMetadata()))->defer('custom-group'),
        ], [], '/users');

        $this->assertArrayNotHasKey('users', $page['props']);
        $this->assertSame(['custom-group' => ['users']], $page['deferredProps']);
    }
}
