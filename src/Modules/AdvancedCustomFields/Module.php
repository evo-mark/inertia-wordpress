<?php

namespace EvoMark\InertiaWordpress\Modules\AdvancedCustomFields;

use EvoMark\InertiaWordpress\Helpers\HookFilters;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;

class Module extends BaseModule
{
    protected string $title = "Advanced Custom Fields";
    protected string $class = "ACF";
    protected string $slug = "acf";
    protected array|string $entry = [
        'advanced-custom-fields-pro/acf.php',
        'acf-pro/acf.php',
        'advanced-custom-fields/acf.php',
    ];
    protected bool $isInternal = true;

    /**
     * Called via action hook
     */
    public function boot(): void
    {
        Inertia::share(
            'acf',
            /**
             * Modify the ACF fields shared with the frontend
             *
             * @param stdClass $data The ACF fields
             *
             * @since 0.8.0
             */
            apply_filters(HookFilters::ACF_SHARE, [
                'post' => $this->getAcfPostFields(),
                'options' => $this->getAcfOptionsPages(),
            ])
        );
    }

    /**
     * Load ACF field values for the current post
     */
    private function getAcfPostFields()
    {
        if (!function_exists('get_field_objects')) {

            /**
             * Modify the ACF fields associated with the current post
             *
             * @param stdClass $data The post fields to share
             *
             * @since 0.8.0
             */
            return apply_filters(HookFilters::ACF_POST_FIELDS, (object) []);
        }
        $acf = get_field_objects();
        $acf = $acf !== false ? $acf : [];
        $results = [];

        foreach ($acf as $key => $field) {
            $results[$key] = $field['value'];
        }

        /**
         * Modify the ACF fields associated with the current post
         *
         * @param stdClass $data The post fields to share
         *
         * @since 0.8.0
         */
        return apply_filters(HookFilters::ACF_POST_FIELDS, (object) $results);
    }

    /**
     * Load ACF fields set on global options pages
     */
    private function getAcfOptionsPages()
    {
        $pages = [];
        if (! $definedPages = acf_get_options_pages()) {

            /**
             * Modify the ACF fields associated with any defined options pages
             *
             * @param stdClass $data The global options pages
             *
             * @since 0.8.0
             */
            return apply_filters(HookFilters::ACF_OPTIONS_PAGES, (object) $pages);
        };

        foreach (array_keys($definedPages) as $key) {
            $fieldGroups = acf_get_field_groups(['options_page' => $key]);

            $fieldsWithValues = [];

            foreach ($fieldGroups as $field_group) {
                $fields = acf_get_fields($field_group['key']);

                if ($fields) {
                    foreach ($fields as $field) {
                        $fieldsWithValues[$field['name']] = get_field($field['name'], 'options');
                    }
                }
            }

            $pages[$key] = $fieldsWithValues;
        }

        /**
         * Modify the ACF fields associated with any defined options pages
         *
         * @param stdClass $data The global options pages
         *
         * @since 0.8.0
         */
        return apply_filters(HookFilters::ACF_OPTIONS_PAGES, (object) $pages);
    }
}
