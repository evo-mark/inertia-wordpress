<?php

namespace EvoMark\InertiaWordpress\Helpers;

class Path
{
    public static function join(...$parts)
    {
        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
