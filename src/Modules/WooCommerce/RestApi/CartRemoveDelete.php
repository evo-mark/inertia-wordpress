<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\RestApi;

use WP_REST_Request;
use EvoWpRestRegistration\BaseRestController;

use function WC;

defined('ABSPATH') or exit;

class CartRemoveDelete extends BaseRestController
{
    protected $path = 'modules/woocommerce/cart/(?P<itemId>[a-zA-Z0-9-]+)';
    protected $methods = 'DELETE';

    public $rules = [
        "itemId" => ["required", "string"],
    ];

    public function authorise()
    {
        return true;
    }

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();

        if (function_exists('WC')) {
            include_once constant('WC_ABSPATH') . 'includes/wc-cart-functions.php';
            include_once constant('WC_ABSPATH') . 'includes/class-wc-cart.php';

            if (is_null(WC()->cart)) {
                wc_load_cart();
            }

            $cart = WC()->cart;

            if (!did_action('woocommerce_load_cart_from_session')) {
                $cart->get_cart();
            }

            $response = $cart->remove_cart_item($validated['item_id']);
        } else {
            wp_send_json_error("WooCommerce is not installed");
        }
    }
}
