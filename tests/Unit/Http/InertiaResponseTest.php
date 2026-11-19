<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Http;

use Brain\Monkey\Functions;
use EvoMark\InertiaWordpress\Data\HttpResponse;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Tests\Stubs\ArrayableValue;
use EvoMark\InertiaWordpress\Tests\Stubs\ExamplePropsProvider;
use EvoMark\InertiaWordpress\Tests\Stubs\TestController;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * The HTTP envelope around an Inertia (XHR) visit: JSON body, headers and the
 * asset-version check. Responses are captured through the ResponseEmitter contract.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/MiddlewareTest.php
 * @see https://inertiajs.com/the-protocol#inertia-responses
 * @see https://inertiajs.com/the-protocol#asset-versioning
 */
class InertiaResponseTest extends TestCase
{
    /**
     * @param array<array-key, mixed>|object $props
     */
    private function visit(string $component = 'User/Edit', array|object $props = []): HttpResponse
    {
        return $this->captureResponse(fn () => (new TestController($component, $props))->handle());
    }

    public function test_inertia_requests_receive_a_json_page_object(): void
    {
        $this->makeInertiaRequest('/user/123');

        $response = $this->visit('User/Edit', ['user' => ['name' => 'Jonathan']]);

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('User/Edit', $response->getBody()['component']);
        $this->assertSame('Jonathan', $response->getBody()['props']['user']['name']);
        $this->assertSame('/user/123', $response->getBody()['url']);
    }

    public function test_inertia_responses_carry_the_inertia_and_vary_headers(): void
    {
        $this->makeInertiaRequest('/user/123');

        $response = $this->visit();

        $this->assertSame('true', $response->getHeader('X-Inertia'));
        $this->assertSame('X-Inertia', $response->getHeader('Vary'));
        $this->assertStringStartsWith('application/json', (string) $response->getHeader('Content-Type'));
    }

    public function test_response_headers_are_case_insensitive(): void
    {
        $this->makeInertiaRequest('/user/123');

        $response = $this->visit();

        $this->assertSame($response->getHeader('X-Inertia'), $response->getHeader('x-inertia'));
    }

    public function test_the_x_inertia_header_alone_identifies_an_inertia_request(): void
    {
        // The protocol only requires X-Inertia; X-Requested-With must not be mandatory.
        $request = $this->makeRequest('/user/123', 'GET', ['X-Inertia' => 'true']);

        $this->assertTrue($request->isInertia());
    }

    public function test_requests_without_the_x_inertia_header_are_not_inertia_requests(): void
    {
        $request = $this->makeRequest('/user/123', 'GET', ['X-Requested-With' => 'XMLHttpRequest']);

        $this->assertFalse($request->isInertia());
    }

    public function test_global_shares_are_included_in_the_response(): void
    {
        $this->makeInertiaRequest('/');

        $props = $this->visit()->getBody()['props'];

        $this->assertArrayHasKey('errors', $props);
        $this->assertArrayHasKey('wp', $props);
    }

    public function test_controllers_accept_arrayable_props(): void
    {
        $this->makeInertiaRequest('/');

        $response = $this->visit('User/Edit', new ArrayableValue(['foo' => 'bar']));

        $this->assertSame('bar', $response->getBody()['props']['foo']);
    }

    public function test_controllers_accept_provides_inertia_properties(): void
    {
        $this->makeInertiaRequest('/');

        $response = $this->visit('User/Edit', [
            'regular' => 'prop',
            new ExamplePropsProvider(['from_object' => 'value']),
            'another' => 'normal_prop',
        ]);

        $props = $response->getBody()['props'];
        $this->assertSame('prop', $props['regular']);
        $this->assertSame('value', $props['from_object']);
        $this->assertSame('normal_prop', $props['another']);
    }

    public function test_the_version_is_optional(): void
    {
        $this->makeInertiaRequest('/');

        $response = $this->visit();

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('User/Edit', $response->getBody()['component']);
    }

    public function test_the_version_can_be_a_number(): void
    {
        $version = 1597347897973;
        $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Version' => $version]);
        Inertia::version($version);

        $response = $this->visit();

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('User/Edit', $response->getBody()['component']);
    }

    public function test_the_version_can_be_a_string(): void
    {
        $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Version' => 'foo-version']);
        Inertia::version('foo-version');

        $response = $this->visit();

        $this->assertSame(200, $response->getStatus());
    }

    public function test_the_version_can_be_a_closure(): void
    {
        $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Version' => hash('xxh128', 'Inertia')]);
        Inertia::version(fn () => hash('xxh128', 'Inertia'));

        $this->assertSame(200, $this->visit()->getStatus());
    }

    public function test_it_will_instruct_inertia_to_reload_on_a_version_mismatch(): void
    {
        $this->makeInertiaRequest('/user/123?tab=profile', 'GET', ['X-Inertia-Version' => '4321']);
        Inertia::version('1234');

        $response = $this->visit();

        $this->assertSame(409, $response->getStatus());
        $this->assertSame(self::HOME_URL . '/user/123?tab=profile', $response->getHeader('X-Inertia-Location'));
        $this->assertEmpty($response->getBody());
    }

    /**
     * Mirrors core get_home_url(): the path is appended to the "home" option, which on a
     * subdirectory install (or a multisite subdirectory site) already ends in that directory.
     */
    private function homeIs(string $home): void
    {
        Functions\when('home_url')->alias(
            fn ($path = '') => $home . ($path === '' ? '' : '/' . ltrim($path, '/'))
        );
    }

    public function test_the_reload_url_is_not_doubled_up_in_a_subdirectory_install(): void
    {
        $this->homeIs('http://example.test/blog');
        $this->makeInertiaRequest('/blog/about/?x=1', 'GET', ['X-Inertia-Version' => 'stale']);
        Inertia::version('fresh');

        $response = $this->visit();

        $this->assertSame(409, $response->getStatus());
        $this->assertSame('http://example.test/blog/about/?x=1', $response->getHeader('X-Inertia-Location'));
    }

    public function test_the_reload_url_keeps_the_home_scheme_and_port(): void
    {
        $this->homeIs('https://example.test:8443');
        $this->makeInertiaRequest('/about', 'GET', ['X-Inertia-Version' => 'stale']);
        Inertia::version('fresh');

        $this->assertSame('https://example.test:8443/about', $this->visit()->getHeader('X-Inertia-Location'));
    }

    public function test_the_reload_url_does_not_trust_the_host_header(): void
    {
        $this->makeInertiaRequest('/about', 'GET', ['X-Inertia-Version' => 'stale', 'Host' => 'attacker.test']);
        Inertia::version('fresh');

        $this->assertSame(self::HOME_URL . '/about', $this->visit()->getHeader('X-Inertia-Location'));
    }

    public function test_a_missing_version_header_is_a_mismatch_when_a_version_is_set(): void
    {
        $this->makeInertiaRequest('/');
        Inertia::version('1234');

        $this->assertSame(409, $this->visit()->getStatus());
    }

    public function test_version_mismatches_are_ignored_for_non_get_requests(): void
    {
        $this->makeInertiaRequest('/users', 'POST', ['X-Inertia-Version' => '4321']);
        Inertia::version('1234');

        $response = $this->visit();

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('User/Edit', $response->getBody()['component']);
    }

    public function test_flash_data_is_preserved_on_a_version_mismatch(): void
    {
        Inertia::flash('message', 'Saved!');
        $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Version' => 'stale']);
        Inertia::version('fresh');

        $this->assertSame(409, $this->visit()->getStatus());

        // The client reloads the page with a full (non-Inertia) visit.
        $request = $this->makeRequest('/');
        Inertia::version('fresh');
        $request->setComponent('User/Edit');

        $this->assertSame(['message' => 'Saved!'], $request->getPageData()['flash']);
    }

    public function test_validation_errors_are_preserved_on_a_version_mismatch(): void
    {
        \EvoMark\InertiaWordpress\Helpers\RequestResponse::setFlashData('errors', [
            'default' => new \EvoMark\InertiaWordpress\Data\MessageBag(['name' => ['Required.']]),
        ]);
        $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Version' => 'stale']);
        Inertia::version('fresh');

        $this->assertSame(409, $this->visit()->getStatus());

        $request = $this->makeRequest('/');
        $request->setGlobalShares();

        $this->assertSame('Required.', Inertia::getShared('errors')()->name);
    }
}
