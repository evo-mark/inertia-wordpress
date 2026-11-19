<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Data\ScrollMetadata;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Props\LazyProp;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Props\OnceProp;
use EvoMark\InertiaWordpress\Props\OptionalProp;
use EvoMark\InertiaWordpress\Props\ScrollProp;
use EvoMark\InertiaWordpress\Request\RequestHandler;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * The static Inertia facade: prop factories, version and URL configuration.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseFactoryTest.php
 */
class InertiaTest extends TestCase
{
    public function test_can_create_lazy_prop(): void
    {
        $lazyProp = Inertia::lazy(fn () => 'A lazy value');

        $this->assertInstanceOf(LazyProp::class, $lazyProp);
        $this->assertSame('A lazy value', $lazyProp());
    }

    public function test_can_create_optional_prop(): void
    {
        $optionalProp = Inertia::optional(fn () => 'An optional value');

        $this->assertInstanceOf(OptionalProp::class, $optionalProp);
        $this->assertSame('An optional value', $optionalProp());
    }

    public function test_can_create_deferred_prop(): void
    {
        $deferredProp = Inertia::defer(fn () => 'A deferred value');

        $this->assertInstanceOf(DeferProp::class, $deferredProp);
        $this->assertSame('default', $deferredProp->group());
    }

    public function test_can_create_deferred_prop_with_custom_group(): void
    {
        $deferredProp = Inertia::defer(fn () => 'A deferred value', 'foo');

        $this->assertInstanceOf(DeferProp::class, $deferredProp);
        $this->assertSame('foo', $deferredProp->group());
    }

    public function test_can_create_merged_prop(): void
    {
        $mergedProp = Inertia::merge(fn () => 'A merged value');

        $this->assertInstanceOf(MergeProp::class, $mergedProp);
        $this->assertTrue($mergedProp->shouldMerge());
        $this->assertFalse($mergedProp->shouldDeepMerge());
    }

    public function test_can_create_deep_merged_prop(): void
    {
        $mergedProp = Inertia::deepMerge(fn () => 'A merged value');

        $this->assertInstanceOf(MergeProp::class, $mergedProp);
        $this->assertTrue($mergedProp->shouldMerge());
        $this->assertTrue($mergedProp->shouldDeepMerge());
    }

    public function test_can_create_deferred_and_merged_prop(): void
    {
        $deferredProp = Inertia::defer(fn () => 'A deferred + merged value')->merge();

        $this->assertInstanceOf(DeferProp::class, $deferredProp);
        $this->assertTrue($deferredProp->shouldMerge());
    }

    public function test_can_create_deferred_and_deep_merged_prop(): void
    {
        $deferredProp = Inertia::defer(fn () => 'A deferred + merged value')->deepMerge();

        $this->assertInstanceOf(DeferProp::class, $deferredProp);
        $this->assertTrue($deferredProp->shouldDeepMerge());
    }

    public function test_can_create_scroll_prop(): void
    {
        $data = ['item1', 'item2'];
        $scrollProp = Inertia::scroll($data, 'data', new ScrollMetadata('paged', null, 2, 1));

        $this->assertInstanceOf(ScrollProp::class, $scrollProp);
        $this->assertSame($data, $scrollProp());
    }

    public function test_can_create_scroll_prop_with_metadata_provider(): void
    {
        $data = ['item1', 'item2'];
        $scrollProp = Inertia::scroll($data, 'data', new ScrollMetadata('custom', 1, 3, 2));

        $this->assertInstanceOf(ScrollProp::class, $scrollProp);
        $this->assertSame($data, $scrollProp());
        $this->assertSame([
            'pageName' => 'custom',
            'previousPage' => 1,
            'nextPage' => 3,
            'currentPage' => 2,
        ], $scrollProp->metadata());
    }

    public function test_can_create_once_prop(): void
    {
        $onceProp = Inertia::once(fn () => 'A once value');

        $this->assertInstanceOf(OnceProp::class, $onceProp);
        $this->assertSame('A once value', $onceProp());
    }

    public function test_can_create_deferred_and_once_prop(): void
    {
        $deferredProp = Inertia::defer(fn () => 'A deferred + once value')->once();

        $this->assertInstanceOf(DeferProp::class, $deferredProp);
        $this->assertTrue($deferredProp->shouldResolveOnce());
    }

    public function test_can_create_always_prop(): void
    {
        $alwaysProp = Inertia::always(fn () => 'An always value');

        $this->assertInstanceOf(AlwaysProp::class, $alwaysProp);
        $this->assertSame('An always value', $alwaysProp());
    }

    public function test_the_version_is_an_empty_string_by_default(): void
    {
        $this->assertSame('', Inertia::getVersion());
    }

    public function test_the_version_can_be_a_string(): void
    {
        Inertia::version('foo-version');

        $this->assertSame('foo-version', Inertia::getVersion());
    }

    public function test_the_version_can_be_a_number(): void
    {
        Inertia::version(1597347897973);

        $this->assertSame('1597347897973', Inertia::getVersion());
    }

    public function test_the_version_can_be_a_closure(): void
    {
        $calls = 0;
        Inertia::version(function () use (&$calls) {
            $calls++;

            return hash('xxh128', 'Inertia');
        });

        $this->assertSame(0, $calls, 'The version closure should be resolved lazily.');
        $this->assertSame(hash('xxh128', 'Inertia'), Inertia::getVersion());
    }

    public function test_the_version_is_included_in_the_page(): void
    {
        Inertia::version(fn () => 'abc123');

        $request = $this->currentRequest();
        $request->setComponent('User/Edit');

        $this->assertSame('abc123', $request->getPageData()['version']);
    }

    public function test_the_url_can_be_resolved_with_a_custom_resolver(): void
    {
        $this->makeInertiaRequest('/user/123');

        Inertia::resolveUrlUsing(function (string $url) {
            $this->assertSame('/user/123', $url);

            return '/my-custom-url';
        });

        $request = $this->currentRequest();
        $request->setComponent('User/Edit');

        $this->assertSame('/my-custom-url', $request->getPageData()['url']);
    }

    public function test_the_url_resolver_receives_the_request_handler(): void
    {
        $request = $this->makeInertiaRequest('/products?utm_source=newsletter&page=2');

        Inertia::resolveUrlUsing(function (string $url, RequestHandler $handler) use ($request) {
            $this->assertSame($request, $handler);

            // e.g. strip tracking parameters from the URL the client stores in history
            [$path, $query] = explode('?', $url, 2);
            parse_str($query, $params);
            unset($params['utm_source']);

            return $path . ($params ? '?' . http_build_query($params) : '');
        });
        $request->setComponent('Products/Index');

        $this->assertSame('/products?page=2', $request->getPageData()['url']);
    }

    public function test_the_default_url_can_be_restored(): void
    {
        $request = $this->makeInertiaRequest('/user/123');
        Inertia::resolveUrlUsing(fn () => '/my-custom-url');
        Inertia::resolveUrlUsing(null);
        $request->setComponent('User/Edit');

        $this->assertSame('/user/123', $request->getPageData()['url']);
    }
}
