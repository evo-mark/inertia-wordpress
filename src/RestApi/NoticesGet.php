<?php

namespace EvoMark\InertiaWordpress\RestApi;

use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;
use EvoMark\InertiaWordpress\Helpers\Settings;

defined('ABSPATH') or exit;

class NoticesGet extends BaseRestController
{
    protected $path = 'notices';
    protected $methods = 'GET';

    public function authorise()
    {
        return current_user_can('manage_options');
    }

    public function handler(WP_REST_Request $request)
    {
        $notices = [];

        $settings = Settings::get(['entry_file', 'root_template', 'templates_directory']);
        $entryFile = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $settings['entry_file'];
        $rootTemplate = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $settings['root_template'];
        $templates = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $settings['templates_directory'];
        $node = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'node_modules';

        if (file_exists($entryFile) === false) {
            $notices[] = "Your theme entry file doesn't appear to exist. Check the location in your Inertia settings page";
        }
        if (file_exists($rootTemplate) === false) {
            $notices[] = "Your root template doesn't appear to exist. Check the location in your Inertia settings page";
        }
        if (file_exists($templates) === false) {
            $notices[] = "Your templates folder doesn't appear to exist. Check the location in your Inertia settings page";
        }
        if (file_exists($node) !== false) {
            $notices[] = "You haven't installed your node dependencies in your theme. Follow the instructions at <a href='https://inertia-wordpress.evomark.co.uk/getting-started/finishing-theme-setup.html' target='_blank'>the documentation site</a> to finish your set up.";
        }

        return wp_send_json_success([
            'notices' => $notices,
        ]);
    }
}
