<?php

namespace EvoMark\InertiaWordpress\Helpers;

class Efficiency
{
    public static $instance = null;

    public static function init()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new static();
        return self::$instance;
    }

    public array $settings;

    public function __construct()
    {
        $this->settings = Settings::get([
            "remove_emojis",
            "remove_jquery",
            "remove_global_styles",
            "load_blocks_separately",
            "blocked_admin_roles",
            "blocked_admin_roles_hide_bar",
        ]);
        $this->boot();
    }

    private function isBlockedFromAdmin(): bool
    {
        $roles = (array) wp_get_current_user()->roles;
        return empty(array_diff($roles, $this->settings['blocked_admin_roles']));
    }

    public function boot()
    {
        if ($this->settings['remove_jquery'] === true && !is_admin()) {
            add_action('wp_enqueue_scripts', function () {
                wp_deregister_script('jquery');
            });
        }

        if (!empty($this->settings['blocked_admin_roles'])) {
            add_action('admin_init', function () {
                if (wp_doing_ajax() || ! is_user_logged_in()) {
                    return;
                }


                if ($this->isBlockedFromAdmin()) {
                    wp_redirect(get_bloginfo('url'));
                }
            });

            if ($this->isBlockedFromAdmin() && $this->settings['blocked_admin_roles_hide_bar'] === true) {
                add_filter('show_admin_bar', '__return_false');
            }
        }

        if ($this->settings['load_blocks_separately'] && !is_admin()) {
            add_filter('should_load_separate_core_block_assets', '__return_true');
        }

        if ($this->settings['remove_emojis'] === true && !is_admin()) {
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
        }

        if ($this->settings['remove_global_styles'] === true && !is_admin()) {
            remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
            remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
            remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
        }
    }
}
