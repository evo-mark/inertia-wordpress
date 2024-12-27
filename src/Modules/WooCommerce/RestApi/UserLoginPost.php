<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\RestApi;

use WP_REST_Request;
use EvoMark\InertiaWordpress\Inertia;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class UserLoginPost extends BaseRestController
{
    protected $path = 'modules/woocommerce/login';
    protected $methods = 'POST';

    protected $rules = [
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
        'remember' => ['nullable', 'boolean'],
        '_nonce' => ['nullable', 'string'],
    ];

    public function authorise()
    {
        return true;
    }

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();
        $validNonce = wp_verify_nonce($validated['_nonce'], 'woocommerce-login');

        $credentials = [
            'user_login'    => trim(wp_unslash($validated['username'])),
            'user_password' => $validated['password'],
            'remember'      => $validated['remember'],
        ];

        $user = wp_signon(apply_filters('woocommerce_login_credentials', $credentials), is_ssl());

        if (is_wp_error($user) || !$validNonce) {
            $error = new \WP_Error('rest_invalid_param', __('Invalid parameters.'), [
                'status' => 400,
                'params'  => [
                    'username' => 'Could not login with these credentials',
                ],
            ]);
            apply_filters('rest_request_after_callbacks', $error, null, $request);
            exit;
        } else {
            Inertia::flash('success', 'Welcome Back');
        }

        return Inertia::back();
    }
}