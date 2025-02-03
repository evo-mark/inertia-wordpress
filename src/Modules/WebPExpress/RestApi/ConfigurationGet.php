<?php

namespace EvoMark\InertiaWordpress\Modules\WebPExpress\RestApi;

use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class ConfigurationGet extends BaseRestController
{
    protected $path = 'modules/webp-express/configuration';
    protected $methods = 'GET';

    public function authorise()
    {
        return current_user_can('manage_options');
    }

    public function handler(WP_REST_Request $request)
    {
        if (class_exists('\WebPExpress\Config') === false) {
            return wp_send_json_error("WebP Express is not installed");
        }

        return wp_send_json_success([
            'config' => \WebPExpress\Config::loadConfigAndFix(false),
        ]);
    }
}
