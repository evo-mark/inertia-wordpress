<?php

namespace EvoMark\InertiaWordpress\Modules\NinjaForms\Resources;

use EvoMark\InertiaWordpress\Contracts\InertiaResource;
use stdClass;

class FormResource implements InertiaResource
{
    public static function collection(?array $forms): array
    {
        if (empty($forms) || !$forms) {
            return [];
        } else {
            return array_map(fn ($m) => self::single($m), $forms);
        }
    }

    public static function single($form): stdClass
    {
        if ($form instanceof \NF_Database_Models_Form === false) {
            return (object) [];
        };

        $actions = Ninja_Forms()->form($form->get_id())->get_actions();
        $settings = self::getSettings($form);
        $fields = FieldResource::collection(Ninja_Forms()->form($form->get_id())->get_fields());


        return (object) [
            'id' => $form->get_id(),
            'title' => $settings['title'],
            'clearComplete' => boolval($settings['clear_complete']),
            'hideComplete' => boolval($settings['hide_complete']),
            'showTitle' => boolval($settings['show_title']),
            'defaultLabelPosition' => $settings['default_label_pos'],
            'titleHeadingLevel' => $settings['form_title_heading_level'],
            'currency' => $settings['currency'],
            'messages' => array_intersect_key($settings, array_flip(self::getMessageFields())),
            'fields' => $fields,
        ];
    }

    public static function getSettings($form)
    {
        $settings = $form->get_settings();

        foreach ($settings as $name => &$value) {
            if (! in_array(
                $name,
                self::getMessageFields()
            )) {
                continue;
            }

            if ($value) {
                $value = esc_html($value);
                continue;
            }

            unset($settings[$name]);
        }

        // Remove the embed_form setting to avoid page-builder conflicts.
        $settings['embed_form'] = '';

        $settings = array_merge(\Ninja_Forms::config('i18nFrontEnd'), $settings);
        return apply_filters('ninja_forms_display_form_settings', $settings, $form->get_id());
    }

    public static function getMessageFields(): array
    {
        return [
            'changeEmailErrorMsg',
            'changeDateErrorMsg',
            'confirmFieldErrorMsg',
            'fieldNumberNumMinError',
            'fieldNumberNumMaxError',
            'fieldNumberIncrementBy',
            'formErrorsCorrectErrors',
            'validateRequiredField',
            'honeypotHoneypotError',
            'fieldsMarkedRequired',
        ];
    }
}
