<?php

namespace EvoMark\InertiaWordpress\Modules\WooCommerce\Resources;

use WP_Post;

;

use EvoMark\InertiaWordpress\Modules\WooCommerce\Utils;
use EvoMark\InertiaWordpress\Resources\TagResource;
use EvoMark\InertiaWordpress\Resources\CategoryResource;
use EvoMark\InertiaWordpress\Resources\ImageResource;

class ProductResource
{
    public static function collection(array | bool $posts, array $args = [])
    {
        if (empty($posts) || !$posts) {
            return [];
        } else {
            return array_map(fn ($post) => self::single($post, $args), $posts);
        }
    }

    public static function single(?WP_Post $productPost = null, $args = [])
    {
        if (is_null($productPost) || !function_exists('wc_get_product')) {
            return (object) [];
        }

        /** @var \WC_Product $product */
        $product = wc_get_product($productPost);

        return (object) [
            'id' => $product->get_id(),
            'skuCode' => $product->get_sku(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'availability' => $product->get_availability(),
            'averageRating' => $product->get_average_rating(),
            'backorders' => $product->get_backorders(),
            'catalogVisibility' => $product->get_catalog_visibility(),
            'categories' => CategoryResource::collection($product->get_category_ids()),
            'changes' => $product->get_changes(),
            'children' => $product->get_children(),
            'crossSellIds' => $product->get_cross_sell_ids(),
            'onSaleFrom' => $product->get_date_on_sale_from(),
            'onSaleTo' => $product->get_date_on_sale_to(),
            'defaultAttributes' => $product->get_default_attributes(),
            'description' => $product->get_description(),
            'dimensions' => $product->get_dimensions(false),
            'weight' => $product->get_weight(),
            'downloadExpiry' => $product->get_download_expiry(),
            'downloadLimit' => $product->get_download_limit(),
            'downloads' => $product->get_downloads(),
            'galleryImageIds' => $product->get_gallery_image_ids(),
            'image' => ImageResource::single($product->get_image_id()),
            'lowStockAmount' => $product->get_low_stock_amount(),
            'minPurchaseQuantity' => $product->get_min_purchase_quantity(),
            'maxPurchaseQuantity' => $product->get_max_purchase_quantity(),
            'order' => $product->get_menu_order(),
            'link' => $product->get_permalink(),
            'prices' => Utils::getPrices($product),
            'purchaseNote' => $product->get_purchase_note(),
            'ratingCount' => $product->get_rating_count(),
            'ratingCounts' => $product->get_rating_counts(),
            'reviewCount' => $product->get_review_count(),
            'shippingClass' => $product->get_shipping_class(),
            'shippingClassId' => $product->get_shipping_class_id(),
            'shortDescription' => $product->get_short_description(),
            'status' => $product->get_status(),
            'stockQuantity' => $product->get_stock_quantity(),
            'stockStatus' => $product->get_stock_status(),
            'tags' => TagResource::collection($product->get_tag_ids()),
            'taxClass' => $product->get_tax_class(),
            'taxStatus' => $product->get_tax_status(),
            'upsellIds' => $product->get_upsell_ids(),
            'isDownloadable' => $product->get_downloadable(),
            'isFeatured' => $product->get_featured(),
            'isReviewable' => $product->get_reviews_allowed(),
            'isSoldIndividually' => $product->get_sold_individually(),
            'isStockManaged' => $product->get_manage_stock(),
            'isVirtual' => $product->get_virtual(),
            'createdAt' => $product->get_date_created(),
            'updatedAt' => $product->get_date_modified(),
        ];
    }
}
