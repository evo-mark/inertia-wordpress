<?php

namespace EvoMark\InertiaWordpress\Helpers;

class Strings
{
    public static function toPascalCase(string $str): string
    {
        $str = ucwords(str_replace(['-', '_'], ' ', $str));
        $str = str_replace(' ', '', $str);
        return $str;
    }
}
