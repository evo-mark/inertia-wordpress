<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce;

use EvoMark\InertiaWordpress\Helpers\Arr;
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
}
