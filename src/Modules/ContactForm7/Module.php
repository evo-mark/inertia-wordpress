<?php

namespace EvoMark\InertiaWordpress\Modules\ContactForm7;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;

class Module extends BaseModule
{
    protected string $class = "WPCF7_ContactForm";
    protected string $slug = "cf7";
    protected array|string $entry = ['contact-form-7/wp-contact-form-7.php'];

    public function init(): void
    {
        add_action('wp_print_scripts', function () {
            wp_dequeue_script('wpcf7-recaptcha');
            wp_dequeue_script('contact-form-7');
        }, 100);

        add_action('wp_print_styles', function () {
            wp_dequeue_style('contact-form-7');
        }, 100);

        Inertia::share('cf7', fn() => [
            'forms' => Utils::getForms()
        ]);
    }
}
