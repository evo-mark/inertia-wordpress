<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Commands;

use EvoMark\InertiaWordpress\Commands\CheckSsrCommand;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Ssr\HttpGateway;
use EvoMark\InertiaWordpress\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use RuntimeException;
use WP_CLI;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/Commands/CheckSsrTest.php
 */
class CheckSsrCommandTest extends TestCase
{
    private MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();
        WP_CLI::reset();

        $this->mock = new MockHandler();
        Container::getInstance()->set(
            HttpGateway::class,
            new HttpGateway(new Client(['handler' => HandlerStack::create($this->mock)]))
        );
    }

    /**
     * @return array<int, string>
     */
    private function messagesOfType(string $type): array
    {
        return array_values(array_map(
            fn ($message) => $message[1],
            array_filter(WP_CLI::$messages, fn ($message) => $message[0] === $type)
        ));
    }

    public function test_it_succeeds_when_the_ssr_server_is_healthy(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->mock->append(new Response(200));

        $this->assertTrue((new CheckSsrCommand())());
        $this->assertSame(['Inertia SSR server is running.'], $this->messagesOfType('success'));
        $this->assertSame([], $this->messagesOfType('warning'));
    }

    public function test_it_errors_when_the_ssr_server_is_not_running(): void
    {
        $this->setSetting('ssr_enabled', true);
        $this->mock->append(new ConnectException('Connection refused', new Request('GET', '/health')));

        try {
            (new CheckSsrCommand())();
            $this->fail('Expected the command to error.');
        } catch (RuntimeException) {
            // WP_CLI::error() exits with a non-zero status in real use.
        }

        $this->assertSame(['Inertia SSR server is not running.'], $this->messagesOfType('error'));
        $this->assertSame([], $this->messagesOfType('success'));
    }

    public function test_it_warns_when_ssr_is_disabled_in_settings(): void
    {
        $this->setSetting('ssr_enabled', false);
        $this->mock->append(new Response(200));

        (new CheckSsrCommand())();

        $this->assertCount(1, $this->messagesOfType('warning'));
        $this->assertStringContainsString('disabled', $this->messagesOfType('warning')[0]);
        $this->assertSame(['Inertia SSR server is running.'], $this->messagesOfType('success'));
    }
}
