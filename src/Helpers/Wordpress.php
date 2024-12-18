<?php

namespace EvoMark\InertiaWordpress\Helpers;

use WP_Post;
use EvoMark\InertiaWordpress\Data\Archive;
use EvoMark\InertiaWordpress\Resources\ImageResource;
use EvoMark\InertiaWordpress\Resources\PostSimpleResource;
use EvoMark\InertiaWordpress\Resources\ArchivePaginationResource;

class Wordpress
{
    public static function getFeaturedImage(\WP_Post|int|null $post)
    {
        $image_id = \get_post_thumbnail_id($post);
        $image = ImageResource::single($image_id, [
            'fallback' => false
        ]);

        return $image;
    }

    public static function filterArchiveTitle($title)
    {
        if (is_category()) {
            $title = single_cat_title('', false);
        } elseif (is_tag()) {
            $title = single_tag_title('', false);
        } elseif (is_author()) {
            $title = '<span class="vcard">' . get_the_author() . '</span>';
        } elseif (is_post_type_archive()) {
            $title = post_type_archive_title('', false);
        } elseif (is_tax()) {
            $title = single_term_title('', false);
        }

        return $title;
    }

    public static function getCurrentArchiveTitle(): string
    {
        add_filter('get_the_archive_title', [__CLASS__, 'filterArchiveTitle']);
        $title = get_the_archive_title();
        remove_filter('get_the_archive_title', [__CLASS__, 'filterArchiveTitle']);
        return $title;
    }

    public static function getArchiveData(string $title = null, array $posts = null)
    {
        if (empty($title)) $title = self::getCurrentArchiveTitle();
        if (empty($posts)) {
            global $wp_query;
            $posts = $wp_query->posts;
        }

        return new Archive($title, PostSimpleResource::collection($posts), ArchivePaginationResource::collection());
    }

    public static function getUserCapabilities(\WP_User $user): array
    {
        if (empty($user)) return [];
        return $user->get_role_caps();
    }

    public static function getGlobalPost()
    {
        global $post;
        return $post;
    }

    public static function getAdminBar()
    {
        /* require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';

        if (! function_exists('wp_get_current_user')) {
            require_once ABSPATH . WPINC . '/pluggable.php';
        }

        $GLOBALS['wp_admin_bar'] = new \WP_Admin_Bar();


        // Render the admin bar
        $GLOBALS['wp_admin_bar']->initialize();
        do_action('admin_bar_init');
        do_action('admin_bar_menu', $GLOBALS['wp_admin_bar']);
        $GLOBALS['wp_admin_bar']->add_menus();

        dd($GLOBALS['wp_admin_bar']);
        ob_start();
        $GLOBALS['wp_admin_bar']->render();
        $html = ob_get_clean();
        dd($html);
        return $html; */
    }
}
