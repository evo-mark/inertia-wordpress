<?php

namespace EvoMark\InertiaWordpress\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Carbon\Carbon;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Contracts\ResponseEmitter;
use EvoMark\InertiaWordpress\Data\HttpResponse;
use EvoMark\InertiaWordpress\Request\RequestHandler;
use EvoMark\InertiaWordpress\Tests\Support\FakeEmitter;
use EvoMark\InertiaWordpress\Tests\Support\ResponseEmitted;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base class for the unit suite.
 *
 * - Boots Brain Monkey and stubs the WordPress functions the request lifecycle touches.
 * - Backs get_option()/transients with in-memory arrays so tests can arrange state.
 * - Builds a fresh RequestHandler per simulated request from $_SERVER, the same way
 *   the plugin does in production (via getallheaders()).
 * - Captures emitted HTTP responses through a fake ResponseEmitter, so code paths that
 *   would normally send headers and exit can be asserted on instead.
 */
abstract class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public const HOME_URL = 'http://example.test';

    /**
     * Mirrors the settings registered in Plugin::registerSettings().
     *
     * @var array<string, array{type: string, default: mixed}>
     */
    protected const REGISTERED_SETTINGS = [
        'ssr_enabled' => ['type' => 'boolean', 'default' => false],
        'ssr_url' => ['type' => 'string', 'default' => 'http://127.0.0.1:13714'],
        'history_encrypt' => ['type' => 'boolean', 'default' => false],
        'use_script_element' => ['type' => 'boolean', 'default' => false],
        'root_template' => ['type' => 'string', 'default' => 'app.php'],
        'entry_file' => ['type' => 'string', 'default' => 'resources/js/main.js'],
        'entry_namespace' => ['type' => 'string', 'default' => 'theme-inertia'],
        'modules' => ['type' => 'array', 'default' => []],
        'templates_directory' => ['type' => 'string', 'default' => 'resources/js/templates'],
        'remove_emojis' => ['type' => 'boolean', 'default' => false],
        'remove_jquery' => ['type' => 'boolean', 'default' => false],
        'remove_global_styles' => ['type' => 'boolean', 'default' => false],
        'load_blocks_separately' => ['type' => 'boolean', 'default' => false],
        'blocked_admin_roles' => ['type' => 'array', 'default' => ['subscriber']],
        'blocked_admin_roles_hide_bar' => ['type' => 'boolean', 'default' => true],
    ];

    /** @var array<string, mixed> */
    protected array $options = [];

    /** @var array<string, mixed> */
    protected array $transients = [];

    protected string|false $referer = false;

    protected ?FakeEmitter $emitter = null;

    /** @var array<int, string> */
    protected array $hookCalls = [];

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->options = [];
        $this->transients = [];
        $this->referer = false;
        $this->emitter = null;
        $this->hookCalls = [];

        $_GET = [];
        $_POST = [];
        $_COOKIE = ['_temp_id' => 'test-visitor'];
        $this->resetServerGlobals();

        $this->stubWordPress();

        // Start each test with a plain, non-Inertia GET to "/".
        $this->makeRequest('/');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Monkey\tearDown();
        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Requests
     | ----------------------------------------------------------------- */

    /**
     * Simulate a new incoming HTTP request and bind a fresh RequestHandler for it.
     *
     * @param array<string, string|int> $headers Header name => value, e.g. ['X-Inertia' => 'true']
     */
    protected function makeRequest(string $uri = '/', string $method = 'GET', array $headers = []): RequestHandler
    {
        $this->resetServerGlobals();

        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);

        foreach ($headers as $name => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $name))] = (string) $value;
        }

        $_GET = [];
        $query = parse_url($uri, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $_GET);
        }

        $handler = new RequestHandler();
        Container::getInstance()->set('requestHandler', $handler);

        return $handler;
    }

    /**
     * Simulate a new Inertia (XHR) request.
     *
     * @param array<string, string|int> $headers
     */
    protected function makeInertiaRequest(string $uri = '/', string $method = 'GET', array $headers = []): RequestHandler
    {
        return $this->makeRequest($uri, $method, ['X-Inertia' => 'true', ...$headers]);
    }

    /**
     * Build the page object for a component/props pair on a fresh request.
     *
     * @param array<array-key, mixed>|object $props
     * @param array<string, string|int> $headers
     * @return array<string, mixed>
     */
    protected function page(
        string $component,
        array|object $props = [],
        array $headers = [],
        string $uri = '/user/123',
        mixed $version = '123',
    ): array {
        $request = $this->makeRequest($uri, 'GET', $headers);
        $request->setVersion($version);
        $request->setComponent($component);
        $request->setProps($props);

        return $request->getPageData();
    }

    protected function currentRequest(): RequestHandler
    {
        return Container::getInstance()->get('requestHandler');
    }

    /* -----------------------------------------------------------------
     |  Responses
     | ----------------------------------------------------------------- */

    /**
     * Bind the fake emitter, run the callback, and return the response it emitted.
     */
    protected function captureResponse(callable $callback): HttpResponse
    {
        $emitter = $this->fakeEmitter();

        try {
            $callback();
        } catch (ResponseEmitted) {
            // Expected: emitting a response halts the request, like exit() would.
        }

        $this->assertNotNull($emitter->last, 'Expected an HTTP response to be emitted, but none was.');

        return $emitter->last;
    }

    protected function fakeEmitter(): FakeEmitter
    {
        if ($this->emitter === null) {
            $this->emitter = new FakeEmitter();
            Container::getInstance()->set(ResponseEmitter::class, $this->emitter);
        }

        return $this->emitter;
    }

    /**
     * Run a callback while capturing anything it echoes.
     */
    protected function captureOutput(callable $callback): string
    {
        ob_start();
        try {
            $callback();
        } finally {
            $output = ob_get_clean();
        }

        return $output;
    }

    /**
     * Pull the page object out of the HTML rendered for a first (non-XHR) visit.
     * Supports both the data-page attribute and the <script type="application/json"> forms.
     *
     * @return array<string, mixed>
     */
    protected function extractPageFromHtml(string $html): array
    {
        // Check the script form first: its tag also has a data-page attribute (holding the id).
        if (preg_match('#<script data-page="[^"]*" type="application/json">(.*?)</script>#s', $html, $matches)) {
            return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        }

        if (preg_match('/data-page="([^"]*)"/', $html, $matches)) {
            return json_decode(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'), true, flags: JSON_THROW_ON_ERROR);
        }

        $this->fail('No Inertia page object found in HTML: ' . $html);
    }

    /* -----------------------------------------------------------------
     |  WordPress state
     | ----------------------------------------------------------------- */

    protected function setSetting(string $name, mixed $value): void
    {
        $this->options['inertia_' . $name] = $value;
    }

    protected function themeDirectory(): string
    {
        return __DIR__ . '/Fixtures/theme';
    }

    protected function freezeTime(string $time = '2026-01-01 12:00:00'): Carbon
    {
        $now = Carbon::parse($time);
        Carbon::setTestNow($now);

        return $now;
    }

    private function resetServerGlobals(): void
    {
        foreach (array_keys($_SERVER) as $key) {
            if (str_starts_with($key, 'HTTP_')) {
                unset($_SERVER[$key]);
            }
        }

        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    private function stubWordPress(): void
    {
        Functions\stubTranslationFunctions();
        Functions\stubEscapeFunctions();

        $registered = [];
        foreach (self::REGISTERED_SETTINGS as $name => $meta) {
            $registered['inertia_' . $name] = [
                'group' => 'inertia',
                'type' => $meta['type'],
                'default' => $meta['default'],
            ];
        }

        Functions\when('get_registered_settings')->justReturn($registered);
        Functions\when('sanitize_option')->returnArg(2);
        Functions\when('get_option')->alias(function ($name, $default = false) use ($registered) {
            if (array_key_exists($name, $this->options)) {
                return $this->options[$name];
            }

            return $registered[$name]['default'] ?? $default;
        });
        Functions\when('update_option')->alias(function ($name, $value) {
            $this->options[$name] = $value;

            return true;
        });

        Functions\when('set_transient')->alias(function ($key, $value) {
            $this->transients[$key] = $value;

            return true;
        });
        Functions\when('get_transient')->alias(fn ($key) => $this->transients[$key] ?? false);
        Functions\when('delete_transient')->alias(function ($key) {
            unset($this->transients[$key]);

            return true;
        });

        Functions\when('home_url')->alias(
            fn ($path = '') => self::HOME_URL . ($path === '' ? '' : '/' . ltrim($path, '/'))
        );
        Functions\when('get_rest_url')->justReturn(self::HOME_URL . '/wp-json/');
        Functions\when('wp_get_referer')->alias(fn () => $this->referer);
        Functions\when('get_stylesheet_directory')->alias(fn () => $this->themeDirectory());

        Functions\when('get_bloginfo')->justReturn('Example');
        Functions\when('is_rtl')->justReturn(false);
        Functions\when('is_admin_bar_showing')->justReturn(false);
        Functions\when('is_user_logged_in')->justReturn(false);
        Functions\when('wp_create_nonce')->justReturn('nonce');
        Functions\when('has_custom_logo')->justReturn(false);
        Functions\when('get_registered_nav_menus')->justReturn([]);
        Functions\when('get_nav_menu_locations')->justReturn([]);
        Functions\when('get_the_ID')->justReturn(false);
        Functions\when('get_page_template_slug')->justReturn('');
        Functions\when('is_wp_error')->justReturn(false);
        Functions\when('wp_admin_bar_comments_menu')->justReturn(null);
        Functions\when('wp_admin_bar_edit_menu')->justReturn(null);

        Functions\when('status_header')->justReturn(null);

        // Output hooks are recorded rather than mocked: a when() stub set here would take
        // precedence over a per-test expect(), so tests assert on $this->hookCalls instead.
        foreach (['wp_head', 'wp_body_open', 'wp_footer'] as $hook) {
            Functions\when($hook)->alias(function () use ($hook) {
                $this->hookCalls[] = $hook;
            });
        }
    }

    /**
     * Assert how many times a stubbed WordPress output hook (wp_head, wp_body_open, wp_footer) ran.
     */
    protected function assertHookCalled(string $hook, int $times = 1): void
    {
        $actual = count(array_filter($this->hookCalls, fn ($called) => $called === $hook));

        $this->assertSame($times, $actual, "Expected {$hook}() to be called {$times} time(s), called {$actual}.");
    }
}
