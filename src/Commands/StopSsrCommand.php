<?php

namespace EvoMark\InertiaWordpress\Commands;

use EvoMark\InertiaWordpress\Helpers\Settings;

defined('\\ABSPATH') || exit;

class StopSsrCommand
{
    /**
     * Stop the Inertia SSR process
     *
     * @when after_wp_load
     */
    public function __invoke($args = [])
    {
        $url = Settings::get('ssr_url') . "/shutdown";

        $ch = curl_init($url);
        curl_exec($ch);

        if (curl_error($ch) !== 'Empty reply from server') {
            \WP_CLI::error('Unable to connect to Inertia SSR server.');
        }

        curl_close($ch);
        \WP_CLI::success("Inertia SSR server stopped.");

        return true;
    }
}
