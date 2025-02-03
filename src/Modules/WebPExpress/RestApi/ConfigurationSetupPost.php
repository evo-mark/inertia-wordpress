<?php

namespace EvoMark\InertiaWordpress\Modules\WebPExpress\RestApi;

use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class ConfigurationSetupPost extends BaseRestController
{
    protected $path = 'modules/webp-express/configuration';
    protected $methods = 'POST';

    public function authorise()
    {
        return current_user_can('manage_options');
    }

    public function handler(WP_REST_Request $request)
    {
        $config = \WebPExpress\Config::loadConfigAndFix(false);

        $config = array_merge($config, [
            "operation-mode" => "cdn-friendly",
            "destination-extension" => "append",
            "enable-logging" => false,
            "prevent-using-webps-larger-than-original" => true,
            "convert-on-upload" => true,
        ]);
        $config['alter-html']['enabled'] = false;
        $config['web-service']['enabled'] = false;

        \WebPExpress\Config::saveConfigurationAndHTAccess($config, false);

        return wp_send_json_success([
            'config' => \WebPExpress\Config::loadConfigAndFix(false),
        ]);
    }
}
