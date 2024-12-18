<?php

/*
 * Plugin Name: Inertia Wordpress
 * Description: Connect an Inertia frontend theme to your Wordpress application, based on Inertia Laravel 2.0.0
 * Version: 1.0.0
 * Requires at least:
 * Requires PHP: 8.2
 * Author:
 * License: apache2
 * License URI: https://directory.fsf.org/wiki/License:Apache2.0
 * Text Domain: inertia-wordpress
 */

use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Plugin;

require_once __DIR__ . "/vendor/autoload.php";

$container = Container::getInstance();
$plugin = $container->get(Plugin::class);
$plugin->setup(__FILE__);
