<?php

namespace EvoMark\InertiaWordpress\Commands;

use WP_CLI;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Ssr\HttpGateway;
use EvoMark\InertiaWordpress\Helpers\Settings;

defined('\\ABSPATH') || exit;

class CheckSsrCommand
{
    /**
     * Check whether the Inertia SSR server is running
     *
     * Exits with a non-zero status when it isn't, for use in scripts.
     *
     * @when after_wp_load
     */
    public function __invoke($args = [])
    {
        if (! Settings::get('ssr_enabled')) {
            WP_CLI::warning("Inertia SSR is disabled in the Inertia settings, so pages won't be server-rendered even if the server is running.");
        }

        $gateway = Container::getInstance()->get(HttpGateway::class);

        if (! $gateway->isHealthy()) {
            WP_CLI::error('Inertia SSR server is not running.');
        }

        WP_CLI::success('Inertia SSR server is running.');

        return true;
    }
}
