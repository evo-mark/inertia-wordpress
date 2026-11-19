<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Http;

use EvoMark\InertiaWordpress\Tests\Stubs\TestController;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * First (non-Inertia) visits render the theme's root template, which calls
 * inertia_head() / inertia_body() — the WordPress equivalents of @inertiaHead / @inertia.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/DirectiveTest.php
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseTest.php
 */
class RootTemplateTest extends TestCase
{
    private function pageFor(string $component = 'Foo/Bar', array $props = ['foo' => 'bar'], string $uri = '/test'): void
    {
        $request = $this->makeRequest($uri);
        $request->setComponent($component);
        $request->setProps($props);
    }

    public function test_non_inertia_requests_render_the_root_template(): void
    {
        $this->makeRequest('/user/123');

        $html = $this->captureOutput(fn () => (new TestController('User/Edit', ['user' => ['name' => 'Jonathan']]))->handle());

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);

        $page = $this->extractPageFromHtml($html);
        $this->assertSame('User/Edit', $page['component']);
        $this->assertSame('Jonathan', $page['props']['user']['name']);
        $this->assertSame('/user/123', $page['url']);
        $this->assertArrayHasKey('errors', $page['props']);
    }

    public function test_the_root_template_can_be_rendered_more_than_once_per_process(): void
    {
        $this->makeRequest('/first');
        $first = $this->captureOutput(fn () => (new TestController('First'))->handle());

        $this->makeRequest('/second');
        $second = $this->captureOutput(fn () => (new TestController('Second'))->handle());

        $this->assertSame('First', $this->extractPageFromHtml($first)['component']);
        $this->assertSame('Second', $this->extractPageFromHtml($second)['component']);
    }

    public function test_the_root_template_can_be_changed_via_settings(): void
    {
        $this->setSetting('root_template', 'custom-root.php');
        $this->makeRequest('/');

        $html = $this->captureOutput(fn () => (new TestController())->handle());

        $this->assertStringContainsString('<!-- custom-root -->', $html);
        $this->assertStringContainsString('<div id="custom"', $html);
    }

    public function test_inertia_body_renders_the_root_element(): void
    {
        $this->pageFor();

        $html = $this->captureOutput(fn () => inertia_body());

        $this->assertSame(
            '<div id="app" data-page="{&quot;component&quot;:&quot;Foo\/Bar&quot;,&quot;props&quot;:{&quot;foo&quot;:&quot;bar&quot;},&quot;url&quot;:&quot;\/test&quot;,&quot;version&quot;:&quot;&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false}"></div>',
            $html
        );
    }

    public function test_inertia_body_can_use_a_different_root_element_id(): void
    {
        $this->pageFor();

        $html = $this->captureOutput(fn () => inertia_body(['id' => 'foo']));

        $this->assertStringStartsWith('<div id="foo" data-page="', $html);
    }

    public function test_inertia_body_can_add_a_class_to_the_root_element(): void
    {
        $this->pageFor();

        $html = $this->captureOutput(fn () => inertia_body(['class' => 'min-h-screen']));

        $this->assertStringStartsWith('<div id="app" class="min-h-screen" data-page="', $html);
    }

    public function test_inertia_body_escapes_html_in_the_page_data(): void
    {
        $this->pageFor('Foo/Bar', ['foo' => '</div><script>alert("xss")</script>']);

        $html = $this->captureOutput(fn () => inertia_body());

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertSame(1, substr_count($html, '</div>'));
        $this->assertSame('</div><script>alert("xss")</script>', $this->extractPageFromHtml($html)['props']['foo']);
    }

    public function test_inertia_body_calls_the_body_open_and_footer_hooks(): void
    {
        $this->pageFor();

        $this->captureOutput(fn () => inertia_body());

        $this->assertHookCalled('wp_body_open');
        $this->assertHookCalled('wp_footer');
    }

    public function test_inertia_body_renders_the_script_element_when_enabled(): void
    {
        $this->setSetting('use_script_element', true);
        $this->pageFor();

        $html = $this->captureOutput(fn () => inertia_body());

        $this->assertSame(
            '<script data-page="app" type="application/json">{"component":"Foo\/Bar","props":{"foo":"bar"},"url":"\/test","version":"","clearHistory":false,"encryptHistory":false}</script><div id="app"></div>',
            $html
        );
    }

    public function test_inertia_body_script_element_puts_the_class_on_the_root_element(): void
    {
        $this->setSetting('use_script_element', true);
        $this->pageFor();

        $html = $this->captureOutput(fn () => inertia_body(['class' => 'min-h-screen']));

        $this->assertStringStartsWith('<script data-page="app" type="application/json">', $html);
        $this->assertStringEndsWith('</script><div id="app" class="min-h-screen"></div>', $html);
    }

    public function test_inertia_body_script_element_uses_the_root_element_id(): void
    {
        $this->setSetting('use_script_element', true);
        $this->pageFor();

        $html = $this->captureOutput(fn () => inertia_body(['id' => 'foo']));

        $this->assertStringStartsWith('<script data-page="foo" type="application/json">', $html);
        $this->assertStringEndsWith('</script><div id="foo"></div>', $html);
    }

    public function test_inertia_body_script_element_escapes_closing_script_tags(): void
    {
        $this->setSetting('use_script_element', true);
        $this->pageFor('Foo/Bar', ['foo' => '</script><!--<script>']);

        $html = $this->captureOutput(fn () => inertia_body());

        // JSON_HEX_TAG-style escaping: also defuses the "<!--<script" double-escape parser state.
        $this->assertStringContainsString('\u003C\/script\u003E\u003C!--\u003Cscript\u003E', $html);
        $this->assertSame(1, substr_count($html, '</script>'));
        $this->assertSame('</script><!--<script>', $this->extractPageFromHtml($html)['props']['foo']);
    }

    public function test_inertia_head_only_runs_wp_head_when_ssr_is_disabled(): void
    {
        $this->pageFor();

        $this->assertSame('', $this->captureOutput(fn () => inertia_head()));
        $this->assertHookCalled('wp_head');
    }
}
