<?php

namespace EvoMark\InertiaWordpress\Contracts;

use stdClass;

interface InertiaResource
{
    public static function collection(?array $items): array;

    public static function single(array|stdClass|null $item): stdClass;
}
