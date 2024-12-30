<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce;

use EvoMark\InertiaWordpress\Helpers\Arr;
use Illuminate\Support\Arr as IlluminateArr;
use EvoMark\InertiaWordpress\Resources\ImageResource;

class Utils
{
    public static function getMiniCart()
    {
        if (!function_exists('WC')) {
            return [];
        }

        $cart = WC()->cart;

        return [
            'items' => collect($cart->get_cart())
                ->values()
                ->map(function ($line) {
                    $line = Arr::convertKeysToCamelCase($line);
                    $productImageId = get_post_thumbnail_id($line['productId']);
                    $line['featuredImage'] = ImageResource::single($productImageId);
                    $product = $line['data'];
                    $line['product'] = [
                        'sku' => $product->get_sku(),
                        'slug' => $product->get_slug(),
                        'type' => $product->get_type(),
                        'title' => $product->get_name(),
                        'status' => $product->get_status(),
                        'description' => $product->get_description(),
                        'prices' => self::getPrices($product),
                        'pl' => get_permalink($product->get_id()),
                    ];
                    unset($line['data']);
                    $line['lineTotalDisplay'] = wc_price($line['lineTotal']);
                    return $line;
                })
                ->toArray(),
            'subtotal' => self::formatPrice($cart->get_subtotal()),
        ];
    }

    public static function formatPrice($price, $args = [])
    {
        $args = apply_filters(
            'wc_price_args',
            wp_parse_args(
                $args,
                [
                    'ex_tax_label'       => false,
                    'currency'           => '',
                    'decimal_separator'  => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals'           => wc_get_price_decimals(),
                    'price_format'       => get_woocommerce_price_format(),
                ]
            )
        );

        $originalPrice = $price;
        $price = (float) $price;
        $price = apply_filters('raw_woocommerce_price', $price, $originalPrice);
        $price = apply_filters('formatted_woocommerce_price', number_format($price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator']), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'], $originalPrice);

        if (apply_filters('woocommerce_price_trim_zeros', false) && $args['decimals'] > 0) {
            $price = wc_trim_zeros($price);
        }

        return $price;
    }

    public static function getPrices(\WC_Product $product)
    {
        return [
            'isOnSale' => $product->is_on_sale(),
            'regularExcludingTax' => wc_get_price_excluding_tax($product, [
                'price' => $product->get_regular_price(),
            ]),
            'saleExcludingTax' => wc_get_price_excluding_tax($product),
            'regularIncludingTax' => wc_get_price_including_tax($product, [
                'price' => $product->get_regular_price(),
            ]),
            'saleIncludingTax' => wc_get_price_including_tax($product),
        ];
    }

    public static function getCustomerAddresses()
    {
        $customerId = get_current_user_id();

        if (! wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
            $addresses = apply_filters(
                'woocommerce_my_account_get_addresses',
                [
                    'billing'  => __('Billing address', 'woocommerce'),
                    'shipping' => __('Shipping address', 'woocommerce'),
                ],
                $customerId
            );
        } else {
            $addresses = apply_filters(
                'woocommerce_my_account_get_addresses',
                [
                    'billing' => __('Billing address', 'woocommerce'),
                ],
                $customerId
            );
        }

        return IlluminateArr::map($addresses, function ($value, $key) {
            $address = self::getCustomerAddress($key);
            return [
                'label' => $value,
                'fields' => self::getAddressFields($key, $address),
                'formatted' => wc_get_account_formatted_address($key),
                'data' => $address,
            ];
        });
    }

    public static function getCustomerAddress($type): array
    {
        $currentUser = wp_get_current_user();
        $type = sanitize_key($type);
        $country      = get_user_meta(get_current_user_id(), $type . '_country', true);

        if (! $country) {
            $country = WC()->countries->get_base_country();
        }

        if ('billing' === $type) {
            $allowedCountries = WC()->countries->get_allowed_countries();

            if (! array_key_exists($country, $allowedCountries)) {
                $country = current(array_keys($allowedCountries));
            }
        }

        if ('shipping' === $type) {
            $allowedCountries = WC()->countries->get_shipping_countries();

            if (! array_key_exists($country, $allowedCountries)) {
                $country = current(array_keys($allowedCountries));
            }
        }

        $addressFields = WC()->countries->get_address_fields($country, $type . '_');
        $address = [];

        foreach ($addressFields as $key => $field) {
            $key = str_replace($type . "_", "", $key);

            $value = get_user_meta(get_current_user_id(), $key, true);

            if (! $value) {
                switch ($key) {
                    case 'email':
                        $value = $currentUser->user_email;
                        break;
                }
            }

            $address[$key] = apply_filters('woocommerce_my_account_edit_address_field_value', $value, $key, $type);
        }


        return Arr::convertKeysToCamelCase($address);
    }

    public static function getAddressFields($type, $address)
    {
        $raw = WC()->countries->get_address_fields($address['country'], $type . '_');
        $fields = IlluminateArr::mapWithKeys($raw, function ($value, $key) use ($type) {
            return [str_replace($type . "_", "", $key) => $value];
        });
        return Arr::convertKeysToCamelCase($fields);
    }
}
