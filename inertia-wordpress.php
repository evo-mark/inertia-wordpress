<?php

/*
 * Plugin Name: Inertia Wordpress
 * Description: Connect an Inertia frontend theme to your Wordpress application, based on Inertia Laravel 2.0.0
 * Version: 0.7.0
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

require_once __DIR__ . "/vendor/autoload.php";

$container = Container::getInstance();
$plugin = $container->get(Plugin::class);
$plugin->setup(__FILE__);
