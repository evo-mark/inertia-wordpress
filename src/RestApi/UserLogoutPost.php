<?php

namespace EvoMark\InertiaWordpress\RestApi;

use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use WP_REST_Request;
use EvoMark\InertiaWordpress\Inertia;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class UserLogoutPost extends BaseRestController
{
    protected $path = 'logout';
    protected $methods = 'POST';

    protected $rules = [
        'nonce' => ['nullable', 'string'],
    ];

    public function authorise()
    {
        return is_user_logged_in();
    }

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();
        $validNonce = wp_verify_nonce($validated['nonce'], 'inertia_logout');

        if (!$validNonce) {
            RequestResponse::backWithErrors($request, [
                '_message' => 'Unable to logout',
            ]);
            exit;
        } else {
            wp_logout();
            Inertia::flash('success', 'You have been logged out');
        }

        return Inertia::back();
    }
}
