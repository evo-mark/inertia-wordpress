<?php

namespace EvoMark\InertiaWordpress\Modules\WebPExpress;

use EvoWpRestRegistration\RestApi;
use EvoMark\InertiaWordpress\Modules\BaseModule;
use EvoMark\InertiaWordpress\Helpers\HookFilters;

class Module extends BaseModule
{
    protected string $title = "WebP Express";
    protected string $class = "WebPExpress\Config";
    protected string $slug = "webp-express";
    protected array|string $entry = ['webp-express/webp-express.php'];
    protected bool $isInternal = true;

    public function init()
    {
        new RestApi([
            'namespace' => 'EvoMark\InertiaWordpress\Modules\WebPExpress\RestApi',
            'version' => 1,
            'directory' => __DIR__ . '/RestApi',
            'base_url' => 'inertia-wordpress',
        ]);
    }

    public function register()
    {
        add_filter(HookFilters::RESOURCES_IMAGE_ITEM, function ($image) {
            $image->original = \WebPExpress\AlterHtmlHelper::getWebPUrl($image->original, $image->original);
            foreach ($image->sizes as &$size) {
                $size['url'] = \WebPExpress\AlterHtmlHelper::getWebPUrl($size['url'], $size['url']);
            }
            return $image;
        });
    }

    public function boot(): void
    {
    }
}
