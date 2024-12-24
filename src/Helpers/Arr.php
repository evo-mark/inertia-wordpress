<?php

namespace EvoMark\InertiaWordpress\Helpers;

class Arr
{
    public static function convertKeysToCamelCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            // Convert key to camelCase
            $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key))));

            // Recursively handle nested arrays
            $result[$camelKey] = is_array($value) ? self::convertKeysToCamelCase($value) : $value;
        }

        return $result;
    }
}
