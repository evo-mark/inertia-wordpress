<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\RestApi;

use EvoWpRestRegistration\BaseRestController;
use WP_REST_Request;

class AddressStatesGet extends BaseRestController
{
    protected $path = 'modules/woocommerce/address/states';
    protected $methods = 'GET';

    protected $rules = [
        'country' => ['required', 'string'],
    ];

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();
        $states = WC()->countries->get_states($validated['country']);

        return wp_send_json_success([
            'states' => $states,
        ]);
    }
}
