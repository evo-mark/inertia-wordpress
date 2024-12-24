<?php

namespace EvoMark\InertiaWordpress\Helpers;

class RequestResponse
{
    public static function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? "GET");
    }

    public static function back($status = 302)
    {
        if (wp_get_referer()) {
            wp_safe_redirect(wp_get_referer(), $status, "Inertia");
            exit;
        } else {
            wp_safe_redirect(home_url(), $status, "Inertia");
            exit;
        }
    }

    public static function getTemporaryId(): string
    {
        if (! isset($_COOKIE['_temp_id'])) {
            $tempId = bin2hex(random_bytes(16));
            setcookie('_temp_id', $tempId, [
                'expires' => 0,
                'path' => COOKIEPATH,
                'domain' => COOKIE_DOMAIN,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
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
}
