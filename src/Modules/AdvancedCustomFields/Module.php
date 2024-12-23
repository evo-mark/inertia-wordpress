<?php

namespace EvoMark\InertiaWordpress\Modules\AdvancedCustomFields;

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
        'advanced-custom-fields/acf.php'
    ];
    protected bool $isInternal = true;

    /**
     * Called via action hook
     */
    public function boot(): void
    {
        Inertia::share('acf', [
            'post' => $this->getAcfPostFields(),
            'options' => $this->getAcfOptionsPages(),
        ]);
    }

    /**
     * Load ACF field values for the current post
     */
    private function getAcfPostFields()
    {
        if (!function_exists('get_field_objects')) {
            return (object) [];
        }
        $acf = get_field_objects();
        $acf = $acf !== false ? $acf : [];
        $results = [];

        foreach ($acf as $key => $field) {
            $results[$key] = $field['value'];
        }

        return (object) $results;
    }

    /**
     * Load ACF fields set on global options pages
     */
    private function getAcfOptionsPages()
    {
        $pages = [];
        if (! $definedPages = acf_get_options_pages()) {
            return (object) $pages;
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

        return $pages;
    }
}
