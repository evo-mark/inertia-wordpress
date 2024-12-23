<?php

namespace EvoMark\InertiaWordpress\Resources;

use EvoMark\InertiaWordpress\Contracts\InertiaResource;
use stdClass;

class CategoryResource implements InertiaResource
{
    public static function collection(?array $categories): array
    {
        if (empty($categories) || !$categories) {
            return [];
        } else {
            return array_map(fn ($c) => self::single($c), $categories);
        }
    }

    public static function single($cat): stdClass
    {
        return (object) [
            'id' => $cat->term_id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'description' => $cat->description,
            'link' => get_category_link($cat),
            'count' => $cat->count,
        ];
    }
}
