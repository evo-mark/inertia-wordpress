<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\RestApi;

use WP_REST_Request;
use EvoMark\InertiaWordpress\Inertia;
use EvoWpRestRegistration\BaseRestController;

use function WC;

defined('ABSPATH') or exit;

class CartUpdatePatch extends BaseRestController
{
    protected $path = 'modules/woocommerce/cart';
    protected $methods = 'PATCH';

    // $variation_id = 0, $variation = array(), $cart_item_data = array()
    protected $rules = [
        'productId' => ['required', 'string'],
        'quantity' => ['required', 'integer'],
    ];

    public function authorise()
    {
        return true;
    }

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();

        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        $updated = WC()->cart->set_quantity($validated['productId'], $validated['quantity']);

        if ($updated) {
            Inertia::flash('success', 'Product quantity updated');
        } else {
            Inertia::flash('error', 'Failed to update quantity');
        }

        return Inertia::back();
    }
}
