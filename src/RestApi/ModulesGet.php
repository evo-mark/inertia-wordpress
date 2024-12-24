<?php

namespace EvoMark\InertiaWordpress\RestApi;

use EvoMark\InertiaWordpress\Container;
use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;
use Illuminate\Support\Arr;

defined('ABSPATH') or exit;

class ModulesGet extends BaseRestController
{
    protected $path = 'modules';
    protected $methods = 'GET';

    public function authorise()
    {
        return current_user_can('manage_options');
    }

    public function handler(WP_REST_Request $request)
    {
        $container = Container::getInstance();
        $modules = $container->get('modules');

        $moduleData = Arr::map($modules->toArray(), function ($module) {
            $instance = $module::create();
            $data = $instance->getData();
            $data['module'] = $module;
            return $data;
        });

        return wp_send_json_success(['modules' => $moduleData]);
    }
}
