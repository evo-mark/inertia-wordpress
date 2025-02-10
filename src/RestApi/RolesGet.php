<?php

namespace EvoMark\InertiaWordpress\RestApi;

use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class RolesGet extends BaseRestController
{
    protected $path = 'roles';
    protected $methods = 'GET';

    public function authorise()
    {
        return current_user_can('manage_options');
    }

    public function handler(WP_REST_Request $request)
    {
        $roles = apply_filters('editable_roles', wp_roles()->roles);

        return wp_send_json_success(['roles' => collect($roles)->map(function ($value, $key) {
            return [
                'title' => $value['name'],
                'value' => $key,
                'disabled' => $key === 'administrator',
            ];
        })->values()]);
    }
}
