<?php

namespace EvoMark\InertiaWordpress\Resources;

use WP_User;

class UserResource
{
    public static function collection(array | bool $users)
    {
        if (empty($users) || !$users) {
            return [];
        } else {
            return array_map(fn ($user) => self::single($user), $users);
        }
    }

    public static function single(WP_User|int $user)
    {
        $userInfo = gettype($user) === 'integer' ? get_userdata($user) : $user;

        return [
            'id' => $userInfo->ID,
            'name' => $userInfo->display_name,
            'description' => get_user_meta($userInfo->ID, 'description', true),
            'website'        => $userInfo->user_url,
            'avatar'      => get_avatar_url($userInfo->ID),
        ];
    }
}
