<?php

namespace EvoMark\InertiaWordpress\Modules\ContactForm7;

use EvoMark\InertiaWordpress\Contracts\InertiaResource;
use stdClass;

class FormFieldResource implements InertiaResource
{
    public static function collection(?array $formFields): array
    {
        if (empty($formFields) || !$formFields) {
            return [];
        } else {
            return array_map(fn ($m) => self::single($m), $formFields);
        }
    }

    public static function single($formField): stdClass
    {
        if ($formField instanceof \WPCF7_FormTag === false) {
            return (object) [];
        };

        return (object) [
            'name' => $formField->name,
            'labels' => $formField->labels,
            'type' => $formField->basetype,
            'required' => $formField->is_required(),
            'content' => $formField->content,
            'options' => $formField->options,
            'values' => $formField->values,
            'attr' => $formField->attr,
        ];
    }
}
