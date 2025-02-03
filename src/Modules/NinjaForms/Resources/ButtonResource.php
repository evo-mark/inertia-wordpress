<?php

namespace EvoMark\InertiaWordpress\Modules\NinjaForms\Resources;

use EvoMark\InertiaWordpress\Contracts\InertiaResource;
use stdClass;

class ButtonResource implements InertiaResource
{
    public static function collection(?array $fields): array
    {
        if (empty($fields) || !$fields) {
            return [];
        } else {
            return collect(array_map(fn ($m) => self::single($m), $fields))->sortBy('order')->values()->toArray();
        }
    }

    public static function single($field): stdClass
    {
        if ($field instanceof \NF_Database_Models_Field === false) {
            return (object) [];
        };

        $settings = $field->get_settings();

        return (object) [
            'id' => $field->get_id(),
            'key' => $settings['key'],
            'type' => $settings['type'],
            'label' => $settings['label'],
            'elementStyles' => self::extractStyles('element_styles_', $settings),
            'labelStyles' => self::extractStyles('label_styles_', $settings),
            'wrapStyles' => self::extractStyles('wrap_styles_', $settings),
            'submitElementHoverStyles' => self::extractStyles('submit_element_hover_styles_', $settings),
            'order' => intval($settings['order']),
        ];
    }

    public static function extractStyles(string $prefix, array $settings)
    {
        $result = [];

        foreach ($settings as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $trimmedKey = substr($key, strlen($prefix));
                $camelCaseKey = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $trimmedKey))));

                $result[$camelCaseKey] = $value;
            }
        }

        return $result;
    }
}
