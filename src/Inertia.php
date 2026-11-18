<?php

namespace EvoMark\InertiaWordpress;

use EvoMark\InertiaWordpress\Data\Archive;
use EvoMark\InertiaWordpress\Data\HttpResponse;
use EvoMark\InertiaWordpress\Request\RequestHandler;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Helpers\Plugin;
use EvoMark\InertiaWordpress\Props\LazyProp;
use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Helpers\Wordpress;
use EvoMark\InertiaWordpress\Modules\BaseModule;
use EvoMark\InertiaWordpress\Props\OptionalProp;
use EvoMark\InertiaWordpress\Resources\PostResource;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Modules\WooCommerce\Resources\ProductResource;
use WP_REST_Request;

/**
 * Facade-like functions
 */
class Inertia
{
    /**
     * Share a value with the frontend using the given key
     * @param  string|array|Arrayable  $key
     * @param  mixed  $value
     */
    public static function share($key, $value = null): void
    {
        $request = inertia_request();
        $request->share($key, $value);
    }

    /**
     * Remove all shared props so far
     */
    public static function flushShared(): void
    {
        $request = inertia_request();
        $request->flushShared();
    }

    /**
     * Make a request to clear the user's visit history
     */
    public static function clearHistory(): void
    {
        $request = inertia_request();
        $request->setClearHistory();

        // Also carry the flag to the next page rendered for this visitor, so that
        // clearing history and then redirecting (e.g. after logout) still works.
        RequestResponse::setFlashData(RequestHandler::CLEAR_HISTORY_FLASH_KEY, true);
    }

    /**
     * Enable the encryption of the user's browser history for this visit
     * @param bool $encrypt To encrypt or not
     */
    public static function encryptHistory($encrypt = true): void
    {
        $request = inertia_request();
        $request->setEncryptHistory($encrypt);
    }

    /**
     * Get the current asset version
     * @return string
     */
    public static function getVersion(): string
    {
        $request = inertia_request();
        return $request->getVersion();
    }

    /**
     * Set the current asset version. A closure is resolved lazily, the first time the
     * version is needed.
     */
    public static function version(\Closure|string|int|null $version): void
    {
        $request = inertia_request();
        $request->setVersion($version);
    }

    /**
     * Generate an Inertia Lazy prop
     */
    public static function lazy(callable $callback): LazyProp
    {
        return new LazyProp($callback);
    }

    public static function optional(callable $callback): OptionalProp
    {
        return new OptionalProp($callback);
    }

    public static function defer(callable $callback, string $group = "default"): DeferProp
    {
        return new DeferProp($callback, $group);
    }

    public static function merge(mixed $value): MergeProp
    {
        return new MergeProp($value);
    }

    public static function always(mixed $value): AlwaysProp
    {
        return new AlwaysProp($value);
    }

    /**
     * Send the visitor to a URL with a full page load, e.g. an external site.
     * Inertia visits get a 409 + X-Inertia-Location (the client then does window.location);
     * other requests get a plain redirect. External URLs are allowed. This ends the request.
     */
    public static function location(string $url)
    {
        $request = inertia_request();

        if ($request->isInertia()) {
            RequestResponse::emit(new HttpResponse(409, [
                ucwords(Header::LOCATION, "-") => $url,
                'Content-Type' => 'text/html; charset=UTF-8',
            ], ''));

            return;
        }

        RequestResponse::emit(HttpResponse::redirect($url, 302, safe: false));
    }

    public static function back(...$params)
    {
        return RequestResponse::back(...$params);
    }

    public static function backWithErrors(WP_REST_Request $request, array $errors)
    {
        return RequestResponse::backWithErrors($request, $errors);
    }

    public static function redirect(string $url, ?int $status = 302)
    {
        return RequestResponse::redirect($url, $status);
    }

    public static function flash(string $key, mixed $value): void
    {
        $data = RequestResponse::getFlashData('flash', []);
        $data = array_merge($data, [
            $key => $value,
        ]);
        RequestResponse::setFlashData('flash', $data);
    }

    /**
     * Gets information, posts and pagination for the current archive
     */
    public static function getArchive(): Archive
    {
        return Wordpress::getArchiveData();
    }

    /**
     * Get a processed post object, defaults to global
     */
    public static function getPost(?\WP_Post $post = null, ?array $args = null)
    {
        if (empty($post)) {
            $post = Wordpress::getGlobalPost();
        }
        if (empty($args)) {
            $args = [
                'author' => true,
                'excerpt' => true,
                'content' => true,
                'comments' => true,
            ];
        }

        return PostResource::single($post, $args);
    }

    public static function getProduct(?\WP_Post $product = null, ?array $args = null)
    {
        if (empty($product)) {
            $product = Wordpress::getGlobalPost();
        }

        return ProductResource::single($product, $args);
    }

    /**
     * Add a new module to Inertia
     */
    public static function addModule(string $module)
    {
        if (!class_exists($module)) {
            throw new \Exception("Unable to find class definition for " . $module);
        } elseif (is_subclass_of($module, BaseModule::class) === false) {
            throw new \Exception($module . " must extend the " . BaseModule::class . " class");
        }

        if (!did_action('inertia_wordpress_modules')) {
            add_action('inertia_wordpress_modules', function () use ($module) {
                Plugin::registerNewModule($module);
            });
        } else {
            Plugin::registerNewModule($module);
        }
    }
}
