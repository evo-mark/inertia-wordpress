<?php

namespace EvoMark\InertiaWordpress\Helpers;

use EvoMark\WpVite\WpVite;
use EvoMark\InertiaWordpress\Container;


class Settings
{
    public static $prefix = "inertia_";

    public static function get(array|string $fields): mixed
    {
        $isSingle = is_string($fields);
        $fields = (array) $fields;

        $payload = [];
        foreach ($fields as $key => $field) {
            $optionName = self::$prefix . $field;

            $payload[$field] = sanitize_option($optionName, get_option($optionName));
        }

        if (true === $isSingle) {
            return $payload[$fields[0]];
        }
        return $payload;
    }

    /**
     * Update a single or multiple plugin settings
     */
    public static function set(string|array $fields, mixed $value = null)
    {
        if (is_string($fields)) {
            $fields = [$fields => $value];
        }

        foreach ($fields as $key => $value) {
            $optionName = self::$prefix . $key;
            update_option($optionName, $value);
        }
    }

    public static function registerPage()
    {
        add_action('admin_menu', function () {
            add_options_page('Inertia', 'Inertia', 'manage_options', 'inertia-wordpress', [__CLASS__, 'renderSettingsPage']);
        });
        add_filter('print_styles_array', [__CLASS__, 'filterAdminStylesheets']);
        add_action('current_screen', [__CLASS__, 'loadResources']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'overrideAdminStyles']);
    }

    public static function renderSettingsPage()
    {
?>
        <div id="inertia-wordpress__admin-page--wrapper" class="v-cloak font-sans"></div>
<?php
    }

    public static function isInertiaMenu()
    {
        if (!is_admin() || function_exists('get_current_screen') === false) {
            return false;
        }
        $screen = get_current_screen();

        if ($screen->base !== "settings_page_inertia-wordpress") {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @filter print_styles_array
     */
    public static function filterAdminStylesheets($stylesArray)
    {
        if (!self::isInertiaMenu()) {
            return $stylesArray;
        } else {
            return array_filter($stylesArray, fn($style) => in_array($style, ['forms']) === false);
        }
    }

    /**
     * @hook current_screen
     */
    public static function loadResources()
    {
        if (!self::isInertiaMenu()) {
            return false;
        }
        $container = Container::getInstance();

        $vite = new WpVite();
        $vite->enqueue([
            'input' => "js/main.js",
            'namespace' => 'evo-core-wp-admin',
            'absolutePath' => Path::join($container->get('env.root'), 'build'),
            'absoluteUrl' => $container->get('env.baseUrl') . 'build',
            'buildDirectory' => 'admin',
            'admin' => true,
            'dependencies' => ['wp-i18n', 'wp-api', 'wp-api-fetch'],
        ]);
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public static function overrideAdminStyles()
    {
        /**
         * Add Admin Panel styles and scripts
         */
        if (!self::isInertiaMenu()) {
            return false;
        }

        add_filter('admin_footer_text', '__return_empty_string', 11);
        add_filter('update_footer', '__return_empty_string', 11);

        add_action('admin_print_styles', function () {
            $base = rest_url('inertia-wordpress/v1/');
            $nonce = wp_create_nonce('wp_rest');
            echo <<<EOF
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                            <style>
                .notice.is-dismissible,.notice.notice-warning { display: none; }
                #wpwrap { background-color: #fafaf9; }
                #wpcontent { padding-left: 0px !important; }
                </style>
                <script>
                    window.\$inertia = {
                        restUrl: "$base",
                        restNonce: "$nonce"
                    }
                </script>
                EOF;
        });
    }
}
