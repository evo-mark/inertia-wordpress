<?php

/**
 * Plugin Name: WP Vite
 * Plugin URI: https://evomark.co.uk
 * Description: Vite support for Wordpress themes and plugins
 * Version: 0.3.1
 * Author: evoMark
 * Author URI: https://evomark.co.uk
 * License: None
 * Text Domain: wp-vite
 *
 * @package wp-vite
 */

require_once "vendor/autoload.php";

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updater = PucFactory::buildUpdateChecker(
    'https://github.com/evo-mark/wp-vite/',
    __FILE__,
    'wp-vite'
);
$updater->setBranch('main');
$updater->getVcsApi()->enableReleaseAssets('/evomark-wp-vite\.zip/');
