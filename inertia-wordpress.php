<?php

/*
 * Plugin Name: Inertia Wordpress
 * Description: Connect an Inertia frontend theme to your Wordpress application, based on Inertia Laravel 2.0.0
 * Version: 0.8.2
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Tested up to: 6.7
 * Author: Evo Mark Ltd
 * License: apache2
 * License URI: https://directory.fsf.org/wiki/License:Apache2.0
 * Text Domain: inertia-wordpress
 */

use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Plugin;

$dir = get_option('inertia-wordpress__autoload-path', __DIR__);
$autoloaded = false;

while ($dir !== '/') {
    $autoloadPath = $dir . '/vendor/autoload.php';

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        update_option('inertia-wordpress__autoload-path', $dir);
        $autoloaded = true;
        break;
    }

    $dir = dirname($dir);
}

if ($autoloaded === false) {
    throw new RuntimeException('No autoload.php file was found');
}

$container = Container::getInstance();
$plugin = $container->get(Plugin::class);
$plugin->setup(__FILE__);
