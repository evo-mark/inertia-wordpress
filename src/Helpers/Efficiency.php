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
        $this->settings = Settings::get(["remove_emojis", "remove_jquery"]);
        $this->boot();
    }

    public function boot()
    {
        if ($this->settings['remove_jquery'] === true && !is_admin()) {
            add_action('wp_enqueue_scripts', function () {
                wp_deregister_script('jquery');
            });
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
    }
}
