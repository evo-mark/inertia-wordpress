<?php

namespace EvoMark\InertiaWordpress\Resources;

class TagResource
{
    public static function collection(array | bool $tags)
    {
        if (empty($tags) || !$tags) return [];
        else return array_map(fn($tag) => self::toArray($tag), $tags);
    }

    public static function toArray($tag)
    {
        return (object) [
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'description' => $tag->description,
            'link' => get_tag_link($tag),
            'count' => $tag->count
        ];
    }
}
