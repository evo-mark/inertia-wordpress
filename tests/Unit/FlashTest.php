<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Tests\Stubs\TestBackedEnum;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * Flash data is a top-level "flash" key on the page object (not a prop), delivered once.
 * In WordPress it survives redirects via a visitor-scoped transient.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseFactoryTest.php
 */
class FlashTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function render(string $component = 'User/Edit'): array
    {
        $request = $this->currentRequest();
        $request->setComponent($component);
        $request->setProps(['user' => 'Jonathan']);

        return $request->getPageData();
    }

    public function test_render_without_flash_does_not_include_flash_key(): void
    {
        $this->makeInertiaRequest('/no-flash');

        $this->assertArrayNotHasKey('flash', $this->render());
    }

    public function test_render_with_flash_includes_flash_in_page(): void
    {
        $this->makeInertiaRequest('/flash-test', 'POST');
        Inertia::flash('type', 'success');
        Inertia::flash(['message' => 'User updated!']);

        $page = $this->render();

        $this->assertSame('Jonathan', $page['props']['user']);
        $this->assertSame(['type' => 'success', 'message' => 'User updated!'], $page['flash']);
    }

    public function test_flash_is_a_page_level_key_after_the_history_flags(): void
    {
        Inertia::flash('message', 'Saved');

        $keys = array_keys($this->render());

        $this->assertSame('flash', end($keys));
    }

    public function test_multiple_flash_calls_are_merged(): void
    {
        $this->makeInertiaRequest('/create', 'POST');
        Inertia::flash('foo', 'value1');
        Inertia::flash('bar', 'value2');

        $this->assertSame(['foo' => 'value1', 'bar' => 'value2'], $this->render('User/Show')['flash']);
    }

    public function test_later_flash_calls_overwrite_earlier_keys(): void
    {
        Inertia::flash('message', 'first');
        Inertia::flash('message', 'second');

        $this->assertSame(['message' => 'second'], $this->render()['flash']);
    }

    public function test_flash_keys_can_be_backed_enums(): void
    {
        Inertia::flash(TestBackedEnum::Foo, 'value');

        $this->assertSame(['foo-value' => 'value'], $this->render()['flash']);
    }

    public function test_flash_data_survives_a_redirect_to_the_next_request(): void
    {
        $this->makeInertiaRequest('/flash-test', 'POST');
        Inertia::flash(['message' => 'Success!']);

        // The browser follows the redirect: a brand new request.
        $this->makeInertiaRequest('/users');

        $this->assertSame(['message' => 'Success!'], $this->render()['flash']);
    }

    public function test_flash_data_is_only_delivered_once(): void
    {
        Inertia::flash('message', 'Saved');
        $this->assertArrayHasKey('flash', $this->render());

        $this->makeInertiaRequest('/next');

        $this->assertArrayNotHasKey('flash', $this->render());
    }

    public function test_flash_data_is_scoped_to_the_visitor(): void
    {
        Inertia::flash('message', 'For the first visitor');

        $_COOKIE['_temp_id'] = 'another-visitor';
        $this->makeInertiaRequest('/');

        $this->assertArrayNotHasKey('flash', $this->render());
    }
}
