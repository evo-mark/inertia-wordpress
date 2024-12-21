<?php

namespace EvoMark\InertiaWordpress\Modules\ContactForm7;

use EvoMark\InertiaWordpress\Helpers\Wordpress;

class Utils
{
    public static function getForms(): array
    {
        $formObjects = \WPCF7_ContactForm::find([]);
        $items = self::formatFormObjects($formObjects);

        return $items;
    }

    public static function getRecaptchaSiteKey()
    {
        $service = \WPCF7_RECAPTCHA::get_instance();
        return $service->get_sitekey();
    }

    /**
     * @param \WPCF7_ContactForm[] $formObjects
     */
    public static function formatFormObjects($formObjects)
    {
        $forms = [];
        foreach ($formObjects as $form) {
            $properties = $form->get_properties();
            $forms[] = [
                'id' => $form->id(),
                'name' => $form->name(),
                'title' => $form->title(),
                'locale' => $form->locale(),
                'fields' => self::formatFormFields($form),
                'messages' => $properties['messages'],
                'additionalSettings' => $properties['additional_settings']
            ];
        }
        return $forms;
    }

    public static function formatFormFields(\WPCF7_ContactForm $form)
    {
        $tags = $form->scan_form_tags();
        return FormFieldResource::collection($tags);
    }
}
