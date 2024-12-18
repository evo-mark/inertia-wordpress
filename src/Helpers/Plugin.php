<?php

namespace EvoMark\InertiaWordpress\Helpers;

use EvoMark\InertiaWordpress\Container;

class Plugin
{
    public static function setPluginVersion(): void
    {
        $container = Container::getInstance();

        if ($container->has('env.version')) {
            return;
        }

        add_action('init', function () use ($container) {
            // Check if the function exists to avoid errors
            if (!function_exists('get_plugin_data')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            // Retrieve plugin data
            $pluginData = get_plugin_data($container->get('env.entry'));

            // Return the version
            $container->set('env.version', $pluginData['Version'] ?? "dev");
        }, 1);
    }
}
