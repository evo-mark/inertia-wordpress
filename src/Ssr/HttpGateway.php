<?php

namespace EvoMark\InertiaWordpress\Ssr;

use Throwable;
use GuzzleHttp\ClientInterface;
use EvoMark\InertiaWordpress\Helpers\Path;
use EvoMark\InertiaWordpress\Helpers\Settings;
use EvoMark\InertiaWordpress\Helpers\HookActions;
use EvoMark\InertiaWordpress\Data\SsrResponse;

/**
 * Talks to the Node SSR server. Every failure returns null (or false), so the page falls
 * back to client-side rendering instead of breaking.
 */
class HttpGateway
{
    public function __construct(protected ClientInterface $client)
    {
    }

    /**
     * Server-render the page, or null if SSR is disabled, the bundle isn't built, or the
     * server fails, times out or returns something unusable.
     *
     * @param  array<string, mixed>  $page
     */
    public function dispatch(array $page): ?SsrResponse
    {
        if (! Settings::get('ssr_enabled')) {
            return null;
        }

        // No built bundle means no server to talk to: skip the connection attempt.
        if (! file_exists(self::bundlePath())) {
            $this->failed('SSR bundle not found at ' . self::bundlePath() . '. Run a build of your theme.');
            return null;
        }

        try {
            $response = $this->client->request('POST', $this->url('/render'), [
                'json' => $page,
                'headers' => ['Content-Type' => 'application/json'],
            ]);
        } catch (Throwable $e) {
            $this->failed('SSR request failed: ' . $e->getMessage(), $e);
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            $this->failed('SSR server responded with status ' . $response->getStatusCode());
            return null;
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data) || ! isset($data['body']) || ! is_string($data['body'])) {
            $this->failed('SSR server returned an invalid response');
            return null;
        }

        return new SsrResponse(array_values((array) ($data['head'] ?? [])), $data['body']);
    }

    /**
     * Whether the SSR server is up and answering its health check.
     */
    public function isHealthy(): bool
    {
        try {
            return $this->client->request('GET', $this->url('/health'))->getStatusCode() === 200;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Where the theme's SSR bundle is built to.
     */
    public static function bundlePath(): string
    {
        return Path::join(wp_upload_dir()['basedir'], 'scw-vite-hmr', Settings::get('entry_namespace'), 'ssr', 'ssr.mjs');
    }

    protected function url(string $path): string
    {
        return rtrim((string) Settings::get('ssr_url'), '/') . $path;
    }

    /**
     * Record a failure: fire a hook, and log it when WP_DEBUG is on, since the page falls
     * back to client-side rendering silently.
     */
    protected function failed(string $message, ?Throwable $exception = null): void
    {
        /**
         * Fired when server-side rendering fails and the page falls back to client-side rendering
         *
         * @since 0.9.0
         *
         * @param string $message What went wrong
         * @param Throwable|null $exception The underlying exception, if any
         */
        do_action(HookActions::SSR_FAILED, $message, $exception);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Inertia] ' . $message);
        }
    }
}
