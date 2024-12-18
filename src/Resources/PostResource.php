<?php

namespace EvoMark\InertiaWordpress\Resources;

use WP_Post;
use EvoMark\InertiaWordpress\Helpers\Wordpress;
use EvoMark\InertiaWordpress\Resources\TagResource;
use EvoMark\InertiaWordpress\Resources\UserResource;
use EvoMark\InertiaWordpress\Resources\CategoryResource;

class PostResource
{
    public static function collection(array | bool $posts, array $args = [])
    {
        if (empty($posts) || !$posts) return [];
        else return array_map(fn($post) => self::single($post, $args), $posts);
    }

    public static function single(?WP_Post $post = null, $args = [])
    {
        if (is_null($post)) return (object) [];
        return (object) [
            'id'             => $post->ID,
            'title'          => $post->post_title,
            'type'           => $post->post_type,
            'parent'         => $post->post_parent,
            'commentsStatus' => $post->comment_status,
            'commentsCount'  => $post->comment_count,
            'featuredImage'  => Wordpress::getFeaturedImage($post),
            'content'        => ($args['content'] ?? false) == true ? apply_filters('the_content', $post->post_content) : null,
            'categories'     => CategoryResource::collection(get_the_category($post->ID)),
            'tags'           => TagResource::collection(get_the_tags($post)),
            'createdAt'      => $post->post_date_gmt,
            'updatedAt'      => $post->post_modified_gmt,
            'status'         => $post->post_status,
            'order'          => $post->menu_order,
            'link'           => get_the_permalink($post),
            'author'         => ($args['author'] ?? false) == true ? (empty($post->post_author) ? [] : UserResource::single($post->post_author)) : null,
            'excerpt'        => ($args['excerpt'] ?? false) == true ? get_the_excerpt($post) : null
        ];
    }
}
