<?php

namespace EvoMark\InertiaWordpress\Modules\NinjaForms;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;
use EvoMark\InertiaWordpress\Modules\NinjaForms\Resources\FormResource;

class Module extends BaseModule
{
    protected string $title = "Ninja Forms";
    protected string $class = "Ninja_Forms";
    protected string $slug = "ninja-forms";
    protected array|string $entry = ['ninja-forms/ninja-forms.php'];
    protected bool $isInternal = true;

    public function register()
    {
    }

    public function boot(): void
    {
        /* $formsBuilder = (new \NinjaForms\Blocks\DataBuilder\FormsBuilderFactory)->make();
        $forms = array_map(function ($form) {
            dd(Ninja_Forms()->form()->get_forms(), );
            $instance = Ninja_Forms()->form()->get_forms()[0];
            return [
                'id' => $form['formID'],
                'title' => $form['formTitle']
            ];
        }, $formsBuilder->get());
        foreach ($forms as &$form) {
            $form['fields'] = array_map(function ($field) {
                return $field->get_settings();
            }, Ninja_Forms()->form($form['id'])->get_fields());
        } */

        Inertia::share('ninjaForms', fn () => [
            'i18n' => \Ninja_Forms::config('i18nFrontEnd'),
            'forms' => FormResource::collection(Ninja_Forms()->form()->get_forms()),
            'restUrl' => get_rest_url(null, '/contact-form-7/v1/'),
        ]);
    }
}
