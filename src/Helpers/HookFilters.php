<?php

namespace EvoMark\InertiaWordpress\Helpers;

class HookFilters
{
    public const SHARE_MENU = "inertia_wordpress_share_menu";
    public const SHARE_MENU_ITEMS_ARGS = "inertia_wordpress_share_menu_items_args";
    public const MENU_ITEM = "inertia_wordpress_menu_item";
    public const PAGE_TEMPLATE = "inertia_page_template";
    public const PAGE_CONTROLLER = "inertia_page_controller";

    public const RESOURCES_IMAGE_ITEM = "inertia_resources_image_item";
    public const RESOURCES_IMAGE_COLLECTION = "inertia_resources_image_collection";
    public const RESOURCES_IMAGE_METADATA = "inertia_resources_image_metadata";

    public const REST_ERROR_BAG = "inertia_rest_error_bag";


    /**
     * Modules
     */
    public const ACF_SHARE = "inertia_modules_acf_share";
    public const ACF_POST_FIELDS = "inertia_modules_acf_post_fields";
    public const ACF_OPTIONS_PAGES = "inertia_modules_acf_options_pages";
}
