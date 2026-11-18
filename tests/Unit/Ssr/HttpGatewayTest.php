<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Ssr;

use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Data\SsrResponse;
use EvoMark\InertiaWordpress\Ssr\HttpGateway;
use EvoMark\InertiaWordpress\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/HttpGatewayTest.php
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/DirectiveTest.php
 */
class HttpGatewayTest extends TestCase
{
    private const PAGE = [
        'component' => 'Foo/Bar',
        'props' => ['foo' => 'bar'],
        'url' => '/test',
        'version' => '',
        'clearHistory' => false,
        'encryptHistory' => false,
    ];

    private MockHandler $mock;

    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    private HttpGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler();
        $stack = HandlerStack::create($this->mock);
        $stack->push(Middleware::history($this->history));

        $this->gateway = new HttpGateway(new Client(['handler' => $stack]));
        Container::getInstance()->set(HttpGateway::class, $this->gateway);
    }

    private function ssrSucceeds(): void
    {
        $this->mock->append(new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'head' => ['<title inertia>SSR Test</title>', '<style></style>'],
            'body' => '<div id="app">SSR Response</div>',
        ])));
    }

    public function test_it_returns_null_when_ssr_is_disabled(): void
    {
        $this->setSetting('ssr_enabled', false);

        $this->assertNull($this->gateway->dispatch(self::PAGE));
        $this->assertCount(0, $this->history, 'No request should be sent when SSR is disabled.');
    }

    public function test_it_posts_the_page_to_the_configured_render_url(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->setSetting('ssr_url', 'http://127.0.0.1:13714');
        $this->ssrSucceeds();

        $response = $this->gateway->dispatch(self::PAGE);

        $this->assertInstanceOf(SsrResponse::class, $response);
        $this->assertSame(['<title inertia>SSR Test</title>', '<style></style>'], $response->head);
        $this->assertSame('<div id="app">SSR Response</div>', $response->body);

        $this->assertCount(1, $this->history);
        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('http://127.0.0.1:13714/render', (string) $request->getUri());
        $this->assertStringContainsString('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame(self::PAGE, json_decode((string) $request->getBody(), true));
    }

    public function test_url_strips_trailing_slash(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->setSetting('ssr_url', 'http://127.0.0.1:13714/');
        $this->ssrSucceeds();

        $this->gateway->dispatch(self::PAGE);

        $this->assertSame('http://127.0.0.1:13714/render', (string) $this->history[0]['request']->getUri());
    }

    public function test_it_returns_null_when_the_http_request_fails(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->mock->append(new Response(500));

        $this->assertNull($this->gateway->dispatch(self::PAGE));
    }

    public function test_it_returns_null_when_the_connection_fails(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->mock->append(new ConnectException('Connection refused', new Request('POST', '/render')));

        $this->assertNull($this->gateway->dispatch(self::PAGE));
    }

    public function test_it_returns_null_when_invalid_json_is_returned(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->mock->append(new Response(200, [], 'invalid json'));

        $this->assertNull($this->gateway->dispatch(self::PAGE));
    }

    public function test_health_check_the_ssr_server(): void
    {
        $this->setSetting('ssr_url', 'http://127.0.0.1:13714/');

        $this->mock->append(new Response(200));
        $this->assertTrue($this->gateway->isHealthy());
        $this->assertSame('GET', $this->history[0]['request']->getMethod());
        $this->assertSame('http://127.0.0.1:13714/health', (string) $this->history[0]['request']->getUri());

        $this->mock->append(new Response(500));
        $this->assertFalse($this->gateway->isHealthy());

        $this->mock->append(new ConnectException('Connection refused', new Request('GET', '/health')));
        $this->assertFalse($this->gateway->isHealthy());
    }

    public function test_inertia_head_renders_server_side_rendered_head_elements_when_enabled(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->ssrSucceeds();
        $request = $this->makeRequest('/test');
        $request->setComponent('Foo/Bar');

        $html = $this->captureOutput(fn () => inertia_head());

        $this->assertStringContainsString("<title inertia>SSR Test</title>\n<style></style>", $html);
    }

    public function test_inertia_body_renders_server_side_rendered_content_when_enabled(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->ssrSucceeds();
        $request = $this->makeRequest('/test');
        $request->setComponent('Foo/Bar');

        $this->captureOutput(fn () => inertia_head());
        $html = $this->captureOutput(fn () => inertia_body());

        $this->assertSame('<div id="app">SSR Response</div>', $html);
    }

    public function test_the_server_side_rendering_request_is_dispatched_only_once_per_request(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->ssrSucceeds();
        $request = $this->makeRequest('/test');
        $request->setComponent('Foo/Bar');

        $this->captureOutput(fn () => inertia_head());
        $this->captureOutput(fn () => inertia_body());

        $this->assertCount(1, $this->history);
    }

    public function test_it_falls_back_to_client_side_rendering_when_ssr_fails(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->mock->append(new Response(500));
        $request = $this->makeRequest('/test');
        $request->setComponent('Foo/Bar');

        $head = $this->captureOutput(fn () => inertia_head());
        $body = $this->captureOutput(fn () => inertia_body());

        $this->assertSame('', $head);
        $this->assertSame('Foo/Bar', $this->extractPageFromHtml($body)['component']);
    }

    public function test_the_ssr_page_matches_the_client_page(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->ssrSucceeds();
        $request = $this->makeRequest('/test');
        $request->setComponent('Foo/Bar');
        $request->setProps(['foo' => 'bar']);

        $this->captureOutput(fn () => inertia_head());

        $this->assertSame(self::PAGE, json_decode((string) $this->history[0]['request']->getBody(), true));
    }
}
