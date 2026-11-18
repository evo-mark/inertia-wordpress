<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Http;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ResponseFactoryTest.php
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/MiddlewareTest.php
 * @see https://inertiajs.com/redirects
 */
class RedirectTest extends TestCase
{
    public function test_location_response_for_inertia_requests(): void
    {
        $this->makeInertiaRequest('/');

        $response = $this->captureResponse(fn () => Inertia::location('https://inertiajs.com'));

        $this->assertSame(409, $response->getStatus());
        $this->assertSame('https://inertiajs.com', $response->getHeader('X-Inertia-Location'));
        $this->assertEmpty($response->getBody());
    }

    public function test_location_response_for_non_inertia_requests(): void
    {
        $this->makeRequest('/');

        $response = $this->captureResponse(fn () => Inertia::location('https://inertiajs.com'));

        $this->assertSame(302, $response->getStatus());
        $this->assertSame('https://inertiajs.com', $response->getHeader('Location'));
    }

    public function test_location_redirects_are_not_modified(): void
    {
        $this->makeRequest('/');

        $response = $this->captureResponse(fn () => Inertia::location('/foo'));

        $this->assertSame(302, $response->getStatus());
        $this->assertSame('/foo', $response->getHeader('Location'));
    }

    public function test_redirect_defaults_to_a_302(): void
    {
        $this->makeInertiaRequest('/', 'POST');

        $response = $this->captureResponse(fn () => Inertia::redirect('/users'));

        $this->assertSame(302, $response->getStatus());
        $this->assertSame('/users', $response->getHeader('Location'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nonPostMutationMethods(): array
    {
        return [
            'PUT' => ['PUT'],
            'PATCH' => ['PATCH'],
            'DELETE' => ['DELETE'],
        ];
    }

    #[DataProvider('nonPostMutationMethods')]
    public function test_inertia_redirects_after_put_patch_delete_are_converted_to_303(string $method): void
    {
        $this->makeInertiaRequest('/users/1', $method);

        $response = $this->captureResponse(fn () => Inertia::redirect('/users'));

        $this->assertSame(303, $response->getStatus());
        $this->assertSame('/users', $response->getHeader('Location'));
    }

    #[DataProvider('nonPostMutationMethods')]
    public function test_non_inertia_redirects_are_not_converted_to_303(string $method): void
    {
        $this->makeRequest('/users/1', $method);

        $response = $this->captureResponse(fn () => Inertia::redirect('/users'));

        $this->assertSame(302, $response->getStatus());
    }

    public function test_only_302_redirects_are_converted_to_303(): void
    {
        $this->makeInertiaRequest('/users/1', 'PUT');

        $response = $this->captureResponse(fn () => Inertia::redirect('/users', 301));

        $this->assertSame(301, $response->getStatus());
    }

    public function test_back_redirects_to_the_referer(): void
    {
        $this->referer = self::HOME_URL . '/foo';
        $this->makeInertiaRequest('/', 'POST');

        $response = $this->captureResponse(fn () => Inertia::back());

        $this->assertSame(302, $response->getStatus());
        $this->assertSame(self::HOME_URL . '/foo', $response->getHeader('Location'));
    }

    public function test_back_after_a_put_is_a_303(): void
    {
        $this->referer = self::HOME_URL . '/foo';
        $this->makeInertiaRequest('/', 'PUT');

        $response = $this->captureResponse(fn () => Inertia::back());

        $this->assertSame(303, $response->getStatus());
        $this->assertSame(self::HOME_URL . '/foo', $response->getHeader('Location'));
    }

    public function test_back_falls_back_to_the_home_url_without_a_referer(): void
    {
        $this->referer = false;
        $this->makeInertiaRequest('/', 'POST');

        $response = $this->captureResponse(fn () => Inertia::back());

        $this->assertSame(self::HOME_URL, $response->getHeader('Location'));
    }

    public function test_flash_data_is_flashed_on_redirect(): void
    {
        $this->makeInertiaRequest('/flash-test', 'POST');

        $response = $this->captureResponse(function () {
            Inertia::flash(['message' => 'Success!']);
            Inertia::redirect('/users');
        });
        $this->assertSame(302, $response->getStatus());

        $request = $this->makeInertiaRequest('/users');
        $request->setComponent('User/Index');

        $this->assertSame(['message' => 'Success!'], $request->getPageData()['flash']);
    }

    public function test_flash_data_is_preserved_on_non_inertia_redirect(): void
    {
        $this->makeRequest('/flash-test', 'POST');

        $this->captureResponse(function () {
            Inertia::flash('message', 'Success!');
            Inertia::redirect('/users');
        });

        $request = $this->makeRequest('/users');
        $request->setComponent('User/Index');

        $this->assertSame(['message' => 'Success!'], $request->getPageData()['flash']);
    }
}
