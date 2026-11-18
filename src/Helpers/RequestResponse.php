<?php

namespace EvoMark\InertiaWordpress\Helpers;

use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Contracts\ResponseEmitter;
use EvoMark\InertiaWordpress\Data\HttpResponse;

class RequestResponse
{
    public static function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? "GET");
    }

    /**
     * Send a response through the bound ResponseEmitter. This ends the request.
     */
    public static function emit(HttpResponse $response): void
    {
        Container::getInstance()->get(ResponseEmitter::class)->emit($response);
    }

    public static function back($status = 302)
    {
        self::redirect(wp_get_referer() ?: home_url(), $status);
    }

    public static function redirect(string $url, ?int $status = 302)
    {
        $status = self::resolveRedirectStatus($status ?? 302);

        self::emit(HttpResponse::redirect($url !== '' ? $url : home_url(), $status));
    }

    /**
     * After a PUT/PATCH/DELETE, an Inertia redirect must be a 303 so the browser follows it
     * with a GET rather than repeating the original method. Other statuses are left alone.
     */
    public static function resolveRedirectStatus(int $status): int
    {
        if (
            $status === 302
            && in_array(self::getMethod(), ['PUT', 'PATCH', 'DELETE'], true)
            && inertia_request()->isInertia()
        ) {
            return 303;
        }

        return $status;
    }

    public static function getTemporaryId(): string
    {
        if (! isset($_COOKIE['_temp_id'])) {
            $tempId = bin2hex(random_bytes(16));

            if (! headers_sent()) {
                setcookie('_temp_id', $tempId, [
                    'expires' => 0,
                    'path' => COOKIEPATH,
                    'domain' => COOKIE_DOMAIN,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
            }

            // Reuse the same ID for the rest of this request rather than minting (and
            // re-sending) a new one on every flash read/write.
            $_COOKIE['_temp_id'] = $tempId;

            return $tempId;
        }
        return $_COOKIE['_temp_id'];
    }

    public static function getFlashKey($key)
    {
        return "inertia_" . self::getTemporaryId() . "_" . $key;
    }

    public static function setFlashData($key, $value)
    {
        $key = self::getFlashKey($key);
        set_transient($key, $value, 10);
    }

    public static function getFlashData($key, $defaultValue = null)
    {
        $key = self::getFlashKey($key);
        $data = get_transient($key);
        if ($data !== false) {
            delete_transient($key);
            return $data;
        }
        return $defaultValue;
    }

    public static function formatErrors(array $errors): array
    {
        $formattedErrors = [];
        foreach ($errors as $key => $error) {
            $formattedErrors[$key] = [$error];
        }
        return $formattedErrors;
    }

    public static function getFormattedErrors(): mixed
    {
        $bags = RequestResponse::getFlashData('errors', (object) []);
        $request = inertia_request();

        return (object) collect($bags)->map(function ($bag) {
            return (object) collect($bag->messages())->map(function ($errors) {
                return $errors[0];
            })->toArray();
        })->pipe(function ($bags) use ($request) {
            if ($bags->has('default') && $request->hasHeader(Header::ERROR_BAG)) {
                return [$request->getHeader(Header::ERROR_BAG) => $bags->get('default')];
            }

            if ($bags->has('default')) {
                return $bags->get('default');
            }

            return $bags->toArray();
        });

        return [];
    }

    public static function backWithErrors($request, $errors)
    {
        $error = new \WP_Error('rest_invalid_param', __('Invalid parameters.'), [
            'status' => 400,
            'params'  => $errors,
        ]);
        apply_filters('rest_request_after_callbacks', $error, null, $request);
        exit;
    }
}
