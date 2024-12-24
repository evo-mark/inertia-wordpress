<?php

namespace EvoMark\InertiaWordpress\Modules\ContactForm7;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Data\MessageBag;
use EvoMark\InertiaWordpress\Modules\BaseModule;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Helpers\Settings;
use Illuminate\Support\Arr;

class Module extends BaseModule
{
    protected string $title = "Contact Form 7";
    protected string $class = "WPCF7_ContactForm";
    protected string $slug = "cf7";
    protected array|string $entry = ['contact-form-7/wp-contact-form-7.php'];
    protected bool $isInternal = true;

    public function register()
    {
        add_filter('wpcf7_feedback_response', [$this, 'handleFeedbackResponse'], 10, 2);
        register_setting('inertia', "inertia_module_cf7_disable_frontend_resources", [
            'type' => 'boolean',
            'default' => true,
            'label' => 'Prevent default resources being enqueued',
        ]);
    }

    public function boot(): void
    {

        $disableResources = Settings::get('module_cf7_disable_frontend_resources');

        if ($disableResources === true) {
            add_action('wp_print_scripts', function () {
                wp_dequeue_script('wpcf7-recaptcha');
                wp_dequeue_script('contact-form-7');
            }, 100);

            add_action('wp_print_styles', function () {
                wp_dequeue_style('contact-form-7');
            }, 100);
        }

        Inertia::share('cf7', fn () => [
            'forms' => Utils::getForms(),
            'recaptchaSiteKey' => Utils::getRecaptchaSiteKey(),
            'recaptchaUrl' => Utils::getRecaptchaUrl(),
            'restUrl' => get_rest_url(null, '/contact-form-7/v1/'),
        ]);
    }

    public function handleFeedbackResponse($response, $result)
    {
        $request = inertia_request();
        if ($request->isInertia() === false) {
            return $response;
        }

        $isFailed = $response['status'] === "validation_failed";
        $isSpam = $response['status'] === "spam";

        if ($isFailed) {
            $bag = $request->getHeader(Header::ERROR_BAG) ?? "default";
            RequestResponse::setFlashData('errors', [
                $bag => new MessageBag(Arr::mapWithKeys($response['invalid_fields'], function ($field) {
                    return [$field['field'] => [$field['message']]];
                })),
            ]);
        } elseif ($isSpam) {
            $bag = $request->getHeader(Header::ERROR_BAG) ?? "default";
            RequestResponse::setFlashData('errors', [
                $bag => new MessageBag(['recaptcha' => [$response['message']]]),
            ]);
        } else {
            Inertia::flash('cf7', $response['message']);
        }
        return Inertia::back();
    }
}
