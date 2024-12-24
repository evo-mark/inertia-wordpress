<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\Commands;

use EvoMark\InertiaWordpress\Commands\BaseCommand;

defined('\\ABSPATH') || exit;

class AddWooCommerceToThemeCommand extends BaseCommand
{
    /**
     * Add WooCommerce controllers/pages to theme
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        return true;
    }
}
