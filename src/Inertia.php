<?php

namespace EvoMark\InertiaWordpress;

use EvoMark\InertiaWordpress\Data\Archive;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Helpers\Wordpress;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Props\LazyProp;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Props\OptionalProp;
use EvoMark\InertiaWordpress\Resources\PostResource;

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

    public static function location(string $url)
    {
        $container = Container::getInstance();
        $request = $container->get('requestHandler');

        if ($request->isInertia()) {
            header(ucwords(Header::LOCATION, "-") . ": " . $url);
            header("Content-Type: text/html; charset=UTF-8", true);
            status_header(409);
            exit;
        }

        wp_redirect($url, 302, 'Inertia');
        exit;
    }

    public static function back(...$params)
    {
        return RequestResponse::back(...$params);
    }

    /**
     * Gets information, posts and pagination for the current archive
     */
    public static function getArchive(): Archive
    {
        return Wordpress::getArchiveData();
    }

    public static function getPost(?\WP_Post $post = null, array $args = null)
    {
        if (empty($post)) {
            $post = Wordpress::getGlobalPost();
        }
        if (empty($args)) {
            $args = [
                'author' => true,
                'excerpt' => true,
                'content' => true
            ];
        }

        return PostResource::single($post, $args);
    }
}
