<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce;

use EvoWpRestRegistration\RestApi;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;

use function WC;

class Module extends BaseModule
{
    protected string $title = "WooCommerce";
    protected string $class = "Automattic\WooCommerce\Autoloader";
    protected string $slug = "woocommerce";
    protected array|string $entry = [
        'woocommerce/woocommerce.php',
    ];
    protected bool $isInternal = true;

    public function register()
    {
        /** @disregard P1011 WP_CLI might not be available  */
        if (defined('WP_CLI') && \WP_CLI) {
            \WP_CLI::add_command('inertia:add-woocommerce', \EvoMark\InertiaWordpress\Modules\WooCommerce\Commands\AddWooCommerceToThemeCommand::class);
        };

        new RestApi([
            'namespace' => 'EvoMark\InertiaWordpress\Modules\WooCommerce\\RestApi\\',
            'version' => 1,
            'directory' => __DIR__ . '/RestApi',
            'base_url' => 'inertia-wordpress',
        ]);

        if (!is_admin()) {
            if (is_null(WC()->cart)) {
                wc_load_cart();
            }
        }
    }

    /**
     * Called via action hook
     */
    public function boot(): void
    {
        Inertia::share('woo', [
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'miniCart' => Utils::getMiniCart(),
            'taxEnabled' => wc_tax_enabled(),
            'checkoutUrl' => wc_get_checkout_url(),
        ]);
    }
}
