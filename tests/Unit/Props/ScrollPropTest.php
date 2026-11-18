<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Contracts\Deferrable;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Props\ScrollProp;
use EvoMark\InertiaWordpress\Tests\Stubs\StaticScrollMetadata;
use EvoMark\InertiaWordpress\Tests\TestCase;
use WP_Query;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ScrollPropTest.php
 */
class ScrollPropTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['wp_query']);
        parent::tearDown();
    }

    public function test_resolves_meta_data_from_the_main_query_by_default(): void
    {
        $GLOBALS['wp_query'] = new WP_Query(['paged' => 2]);
        $GLOBALS['wp_query']->max_num_pages = 5;

        $scrollProp = new ScrollProp(['data' => []]);

        $this->assertSame([
            'pageName' => 'paged',
            'previousPage' => 1,
            'nextPage' => 3,
            'currentPage' => 2,
        ], $scrollProp->metadata());
    }

    public function test_resolves_custom_meta_data(): void
    {
        $scrollProp = new ScrollProp([], 'data', new StaticScrollMetadata('usersPage', 10, 12, 11));

        $this->assertSame([
            'pageName' => 'usersPage',
            'previousPage' => 10,
            'nextPage' => 12,
            'currentPage' => 11,
        ], $scrollProp->metadata());
    }

    public function test_resolves_meta_data_with_callable_provider(): void
    {
        $scrollProp = new ScrollProp([], 'data', fn () => new StaticScrollMetadata('callablePage', 5, 7, 6));

        $this->assertSame([
            'pageName' => 'callablePage',
            'previousPage' => 5,
            'nextPage' => 7,
            'currentPage' => 6,
        ], $scrollProp->metadata());
    }

    public function test_is_mergeable_at_the_wrapper_path(): void
    {
        $scrollProp = new ScrollProp(['data' => []]);

        $this->assertInstanceOf(Mergeable::class, $scrollProp);
        $this->assertTrue($scrollProp->shouldMerge());
        $this->assertFalse($scrollProp->appendsAtRoot());
    }

    public function test_can_set_the_merge_intent_based_on_the_merge_intent_header(): void
    {
        $metadata = new StaticScrollMetadata();

        // Append intent without header
        $appendProp = new ScrollProp([], 'data', $metadata);
        $appendProp->configureMergeIntent();
        $this->assertContains('data', $appendProp->appendsAtPaths());
        $this->assertEmpty($appendProp->prependsAtPaths());

        // Append intent with explicit header
        $this->makeInertiaRequest('/', 'GET', [Header::INFINITE_SCROLL_MERGE_INTENT => 'append']);
        $appendProp = new ScrollProp([], 'data', $metadata);
        $appendProp->configureMergeIntent();
        $this->assertContains('data', $appendProp->appendsAtPaths());
        $this->assertEmpty($appendProp->prependsAtPaths());

        // Prepend intent
        $this->makeInertiaRequest('/', 'GET', [Header::INFINITE_SCROLL_MERGE_INTENT => 'prepend']);
        $prependProp = new ScrollProp([], 'data', $metadata);
        $prependProp->configureMergeIntent();
        $this->assertContains('data', $prependProp->prependsAtPaths());
        $this->assertEmpty($prependProp->appendsAtPaths());

        // Prepend intent with custom wrapper
        $prependProp = new ScrollProp([], 'items', $metadata);
        $prependProp->configureMergeIntent();
        $this->assertContains('items', $prependProp->prependsAtPaths());
        $this->assertEmpty($prependProp->appendsAtPaths());
    }

    public function test_scroll_prop_value_is_resolved_only_once(): void
    {
        $callCount = 0;

        $scrollProp = new ScrollProp(function () use (&$callCount) {
            $callCount++;

            return ['item1', 'item2', 'item3'];
        }, 'data', new StaticScrollMetadata());

        $value1 = $scrollProp();
        $value2 = $scrollProp();
        $value3 = $scrollProp();

        $this->assertSame(1, $callCount, 'Scroll prop value callback should only be executed once');
        $this->assertSame($value1, $value2);
        $this->assertSame($value2, $value3);
        $this->assertSame(['item1', 'item2', 'item3'], $value1);
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $scrollProp = new ScrollProp('date', 'data', new StaticScrollMetadata());

        $this->assertSame('date', $scrollProp());
    }

    public function test_is_not_deferred_by_default(): void
    {
        $scrollProp = new ScrollProp([], 'data', new StaticScrollMetadata());

        $this->assertInstanceOf(Deferrable::class, $scrollProp);
        $this->assertFalse($scrollProp->shouldDefer());
    }

    public function test_can_be_deferred(): void
    {
        $scrollProp = (new ScrollProp([], 'data', new StaticScrollMetadata()))->defer();

        $this->assertTrue($scrollProp->shouldDefer());
        $this->assertSame('default', $scrollProp->group());
    }

    public function test_can_be_deferred_with_a_custom_group(): void
    {
        $scrollProp = (new ScrollProp([], 'data', new StaticScrollMetadata()))->defer('custom-group');

        $this->assertTrue($scrollProp->shouldDefer());
        $this->assertSame('custom-group', $scrollProp->group());
    }
}
