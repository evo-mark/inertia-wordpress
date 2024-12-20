<?php

namespace EvoMark\InertiaWordpress\Resources;

use EvoMark\InertiaWordpress\Contracts\InertiaResource;
use stdClass;

class MenuItemResource implements InertiaResource
{
    public static function collection(?array $menus): array
    {
        if (empty($menus) || !$menus) return [];
        else return array_map(fn($m) => self::single($m), $menus);
    }

    public static function single($menu): stdClass
    {
        if (is_integer($menu)) {
            $menu = get_post($menu);
        }

        return (object) [
            'id' => $menu->ID,
            'parent' => $menu->menu_item_parent,
            'label' => $menu->title,
            'slug' => $menu->post_name,
            'type' => $menu->type,
            'typeLabel' => $menu->type_label,
            'url' => $menu->url,
            'target' => $menu->target,
            'rel' => $menu->xfn,
            'classes' => esc_attr($menu->classes ? implode(' ', $menu->classes) : '')
        ];
    }
}
