<?php

/**
 * PHPUnit bootstrap for the unit suite.
 *
 * WordPress is not loaded. WP functions are stubbed per-test with Brain Monkey
 * (see tests/TestCase.php), and a handful of WP classes have minimal doubles in
 * tests/Stubs/WordPress.php.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

defined('ABSPATH') || define('ABSPATH', sys_get_temp_dir() . '/inertia-wordpress-tests/');
defined('COOKIEPATH') || define('COOKIEPATH', '/');
defined('COOKIE_DOMAIN') || define('COOKIE_DOMAIN', '');
defined('HOUR_IN_SECONDS') || define('HOUR_IN_SECONDS', 3600);

require_once __DIR__ . '/Stubs/WordPress.php';
