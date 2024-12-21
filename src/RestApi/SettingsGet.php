<?php

namespace EvoMark\InertiaWordpress\RestApi;

use EvoMark\InertiaWordpress\Helpers\Settings;
use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class SettingsGet extends BaseRestController
{
    protected $path = 'settings';
    protected $methods = 'GET';

    protected $rules = [
        'fields' => ['required', 'array'],
    ];

    public function authorise()
    {
        return current_user_can('manage_options');
    }

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();

        $settings = Settings::get($validated['fields']);

        return wp_send_json_success(['settings' => $settings]);
    }
}
