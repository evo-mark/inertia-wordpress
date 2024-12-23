<?php

namespace EvoMark\InertiaWordpress\Commands;

use WP_CLI;
use Symfony\Component\Process\Process;
use EvoMark\InertiaWordpress\Helpers\Path;
use EvoMark\InertiaWordpress\Helpers\Settings;
use EvoMark\InertiaWordpress\Exceptions\SsrException;

defined('\\ABSPATH') || exit;

class StartSsrCommand
{
    /**
     * Start the Inertia SSR process
     *
     * @when after_wp_load
     */
    public function __invoke($args = [])
    {
        $isEnabled = Settings::get('ssr_enabled');
        if (!$isEnabled) {
            WP_CLI::error("Inertia SSR is not enabled. Enable it via the Inertia settings pages in your Wordpress admin area");
        }

        $namespace = Settings::get('entry_namespace');
        $target = Path::join(wp_upload_dir()['basedir'], 'scw-vite-hmr', $namespace, 'ssr', 'ssr.mjs');

        if (!file_exists($target)) {
            WP_CLI::error("Couldn't find Inertia SSR file. Ensure you have run a build of your theme and try again.");
        }

        try {
            // WP_CLI::runcommand('inertia:stop-ssr');
        } catch (\Exception $e) {
            //
        }

        $process = new Process(['node', $target]);
        $process->setTimeout(null);
        $process->start();

        if (extension_loaded('pcntl')) {
            $stop = function () use ($process) {
                $process->stop();
            };
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, $stop);
            pcntl_signal(SIGQUIT, $stop);
            pcntl_signal(SIGTERM, $stop);
        }

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                WP_CLI::log(trim($data));
            } else {
                WP_CLI::error(trim($data));
                throw new SsrException($data);
            }
        }

        return true;
    }
}
