<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce;

use EvoMark\InertiaWordpress\Helpers\HookFilters;
use EvoWpRestRegistration\RestApi;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;
use EvoMark\InertiaWordpress\Theme\Utils as ThemeUtils;
use WC_Countries;

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

        if (!is_admin() && is_null(WC()->cart)) {
            add_action('woocommerce_init', 'wc_load_cart');
        }

        add_action('after_setup_theme', fn () => add_theme_support('woocommerce'));
        add_filter(HookFilters::PAGE_CONTROLLER, [$this, 'checkForSubPage'], 10, 2);
    }

    /**
     * Called via action hook
     */
    public function boot(): void
    {
        $wc = new WC_Countries();

        Inertia::share('woo', [
            'checkoutUrl' => wc_get_checkout_url(),
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'cart' => Utils::getMiniCart(),
            'isTaxEnabled' => wc_tax_enabled(),
            'taxName' => $wc->tax_or_vat(),
            'storeAddress' => [
                'address_1' => $wc->get_base_address(),
                'address_2' => $wc->get_base_address_2(),
                'city' => $wc->get_base_city(),
                'state' => $wc->get_base_state(),
                'country' => $wc->get_base_country(),
                'postcode' => $wc->get_base_postcode(),
            ],
            'dimensionsUnit' => \Automattic\WooCommerce\Utilities\I18nUtil::get_dimensions_unit_label(get_option('woocommerce_dimension_unit')),
            'weightUnit' => \Automattic\WooCommerce\Utilities\I18nUtil::get_weight_unit_label(get_option('woocommerce_weight_unit')),
            'reviews' => [
                'enabled' => get_option('woocommerce_enable_reviews') === 'yes',
                'showVerifiedOwnerLabel' => get_option('woocommerce_review_rating_verification_label') === 'yes',
                'verificationRequired' => get_option('woocommerce_review_rating_verification_required') === 'yes',
                'ratingsEnabled' => get_option('woocommerce_enable_review_rating') === 'yes',
                'ratingsRequired' => get_option('woocommerce_review_rating_required') === 'yes',
            ],
            'isLive' => get_option('woocommerce_coming_soon') !== 'yes',
            'nonces' => [
                'login' => wp_create_nonce('woocommerce-login'),
            ],
            'menus' => fn () => [
                'account' => $this->getAccountMenuItems(),
            ],
        ]);
    }

    public function checkForSubPage($class, $filename)
    {
        $originalClass = $class;
        $needle = 'page-my-account.php';

        if (str_ends_with($filename, $needle) === false) {
            return $class;
        }
        $endpoint = WC()->query->get_current_endpoint();
        $query = get_query_var($endpoint);

        switch ($endpoint) {
            case "edit-address":
                $filename = str_replace($needle, 'account/edit-address.php', $filename);
                break;
        }

        $class = ThemeUtils::getClass($filename);

        if (empty($class) || in_array('EvoMark\InertiaWordpress\Contracts\InertiaControllerContract', class_implements($class)) === false) {
            return $originalClass;
        }

        $controller = new $class();
        echo $controller->handle();
        exit;
    }

    public function getAccountMenuItems(): array
    {
        $items = wc_get_account_menu_items();
        $urls = [];
        foreach ($items as $endpoint => $label) {
            $urls[] = [
                'label' => $label,
                'href' => esc_url(wc_get_account_endpoint_url($endpoint)),
            ];
        }

        return $urls;
    }
}
