<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/MergePropTest.php
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/DeepMergePropTest.php
 */
class MergePropTest extends TestCase
{
    public function test_can_invoke_with_a_callback(): void
    {
        $mergeProp = new MergeProp(fn () => 'A merge prop value');

        $this->assertSame('A merge prop value', $mergeProp());
    }

    public function test_can_invoke_with_a_non_callback(): void
    {
        $mergeProp = new MergeProp(['key' => 'value']);

        $this->assertSame(['key' => 'value'], $mergeProp());
    }

    public function test_string_function_names_are_not_invoked(): void
    {
        $mergeProp = new MergeProp('date');

        $this->assertSame('date', $mergeProp());
    }

    public function test_is_mergeable_and_included_on_first_load(): void
    {
        $mergeProp = new MergeProp([]);

        $this->assertInstanceOf(Mergeable::class, $mergeProp);
        $this->assertNotInstanceOf(IgnoreFirstLoad::class, $mergeProp);
        $this->assertTrue($mergeProp->shouldMerge());
        $this->assertFalse($mergeProp->shouldDeepMerge());
    }

    public function test_appends_by_default(): void
    {
        $mergeProp = new MergeProp([]);

        $this->assertTrue($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame([], $mergeProp->appendsAtPaths());
        $this->assertSame([], $mergeProp->prependsAtPaths());
        $this->assertSame([], $mergeProp->matchesOn());
    }

    public function test_prepends(): void
    {
        $mergeProp = (new MergeProp([]))->prepend();

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertTrue($mergeProp->prependsAtRoot());
        $this->assertSame([], $mergeProp->appendsAtPaths());
        $this->assertSame([], $mergeProp->prependsAtPaths());
        $this->assertSame([], $mergeProp->matchesOn());
    }

    public function test_appends_with_nested_merge_paths(): void
    {
        $mergeProp = (new MergeProp([]))->append('data');

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame(['data'], $mergeProp->appendsAtPaths());
        $this->assertSame([], $mergeProp->prependsAtPaths());
        $this->assertSame([], $mergeProp->matchesOn());
    }

    public function test_appends_with_nested_merge_paths_and_match_on(): void
    {
        $mergeProp = (new MergeProp([]))->append('data', 'id');

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame(['data'], $mergeProp->appendsAtPaths());
        $this->assertSame([], $mergeProp->prependsAtPaths());
        $this->assertSame(['data.id'], $mergeProp->matchesOn());
    }

    public function test_prepends_with_nested_merge_paths(): void
    {
        $mergeProp = (new MergeProp([]))->prepend('data');

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame([], $mergeProp->appendsAtPaths());
        $this->assertSame(['data'], $mergeProp->prependsAtPaths());
        $this->assertSame([], $mergeProp->matchesOn());
    }

    public function test_prepends_with_nested_merge_paths_and_match_on(): void
    {
        $mergeProp = (new MergeProp([]))->prepend('data', 'id');

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame([], $mergeProp->appendsAtPaths());
        $this->assertSame(['data'], $mergeProp->prependsAtPaths());
        $this->assertSame(['data.id'], $mergeProp->matchesOn());
    }

    public function test_append_with_nested_merge_paths_as_array(): void
    {
        $mergeProp = (new MergeProp([]))->append(['data', 'items']);

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame(['data', 'items'], $mergeProp->appendsAtPaths());
        $this->assertSame([], $mergeProp->prependsAtPaths());
        $this->assertSame([], $mergeProp->matchesOn());
    }

    public function test_append_with_nested_merge_paths_and_match_on_as_array(): void
    {
        $mergeProp = (new MergeProp([]))->append(['data' => 'id', 'items' => 'uid']);

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame(['data', 'items'], $mergeProp->appendsAtPaths());
        $this->assertSame([], $mergeProp->prependsAtPaths());
        $this->assertSame(['data.id', 'items.uid'], $mergeProp->matchesOn());
    }

    public function test_prepend_with_nested_merge_paths_as_array(): void
    {
        $mergeProp = (new MergeProp([]))->prepend(['data', 'items']);

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame([], $mergeProp->appendsAtPaths());
        $this->assertSame(['data', 'items'], $mergeProp->prependsAtPaths());
        $this->assertSame([], $mergeProp->matchesOn());
    }

    public function test_prepend_with_nested_merge_paths_and_match_on_as_array(): void
    {
        $mergeProp = (new MergeProp([]))->prepend(['data' => 'id', 'items' => 'uid']);

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame([], $mergeProp->appendsAtPaths());
        $this->assertSame(['data', 'items'], $mergeProp->prependsAtPaths());
        $this->assertSame(['data.id', 'items.uid'], $mergeProp->matchesOn());
    }

    public function test_mix_of_append_and_prepend_with_nested_merge_paths_and_match_on_as_array(): void
    {
        $mergeProp = (new MergeProp([]))
            ->append('data')
            ->append('users', 'id')
            ->append(['items' => 'uid', 'posts'])
            ->prepend('categories')
            ->prepend('companies', 'id')
            ->prepend(['tags' => 'name', 'comments']);

        $this->assertFalse($mergeProp->appendsAtRoot());
        $this->assertFalse($mergeProp->prependsAtRoot());
        $this->assertSame(['data', 'users', 'items', 'posts'], $mergeProp->appendsAtPaths());
        $this->assertSame(['categories', 'companies', 'tags', 'comments'], $mergeProp->prependsAtPaths());
        $this->assertSame(['users.id', 'items.uid', 'companies.id', 'tags.name'], $mergeProp->matchesOn());
    }

    public function test_can_deep_merge(): void
    {
        $mergeProp = (new MergeProp(fn () => 'A merge prop value'))->deepMerge();

        $this->assertSame('A merge prop value', $mergeProp());
        $this->assertTrue($mergeProp->shouldMerge());
        $this->assertTrue($mergeProp->shouldDeepMerge());
    }

    public function test_can_use_single_string_as_key_to_match_on(): void
    {
        $mergeProp = (new MergeProp(['key' => 'value']))->matchOn('key');

        $this->assertSame(['key'], $mergeProp->matchesOn());
    }

    public function test_can_use_an_array_of_strings_as_keys_to_match_on(): void
    {
        $mergeProp = (new MergeProp(['key' => 'value']))->matchOn(['key', 'anotherKey']);

        $this->assertSame(['key', 'anotherKey'], $mergeProp->matchesOn());
    }

    public function test_is_onceable(): void
    {
        $mergeProp = (new MergeProp(fn () => []))
            ->once()
            ->as('custom-key')
            ->until(60);

        $this->assertInstanceOf(Onceable::class, $mergeProp);
        $this->assertTrue($mergeProp->shouldResolveOnce());
        $this->assertSame('custom-key', $mergeProp->getKey());
        $this->assertNotNull($mergeProp->expiresAt());
    }
}
