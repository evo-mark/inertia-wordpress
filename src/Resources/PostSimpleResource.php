<?php

namespace EvoMark\InertiaWordpress\Resources;

use WP_Post;
use EvoMark\InertiaWordpress\Helpers\Wordpress;

class PostSimpleResource
{
    public static function collection(array | bool $posts, array $args = [])
    {
        if (empty($posts) || !$posts) {
            return [];
        } else {
            return array_map(fn ($post) => self::single($post, $args), $posts);
        }
    }

    public static function single(?WP_Post $post = null, $args = [])
    {
        if (is_null($post)) {
            return (object) [];
        }
        return (object) [
            'id'            => $post->ID,
            'title'         => $post->post_title,
            'type'          => $post->post_type,
            'featuredImage' => Wordpress::getFeaturedImage($post),
            'link'          => get_the_permalink($post),
            'excerpt'       => ($args['excerpt'] ?? false) == true ? get_the_excerpt($post) : null,
        ];
    }
}
