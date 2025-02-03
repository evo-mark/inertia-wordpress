<?php

namespace EvoMark\InertiaWordpress\Helpers;

use EvoMark\WpVite\WpVite;
use EvoMark\InertiaWordpress\Container;

class Admin
{
    // material-design-icons: chevron-double-right, converted at https://base64.guru/converter/encode/image/svg
    /* cspell:disable-next-line */
    public static $baseIcon = "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iYmxhY2siIGQ9Ik01LjU5LDcuNDFMNyw2TDEzLDEyTDcsMThMNS41OSwxNi41OUwxMC4xNywxMkw1LjU5LDcuNDFNMTEuNTksNy40MUwxMyw2TDE5LDEyTDEzLDE4TDExLjU5LDE2LjU5TDE2LjE3LDEyTDExLjU5LDcuNDFaIiAvPjwvc3ZnPg==";


    /**
     * @hook plugins_loaded
     */
    public static function setup()
    {
        add_action('admin_menu', function () {
            $icon = self::get_icon_uri();

            add_menu_page(
                "Wordpress Inertia",
                "Inertia",
                "manage_options",
                "inertia-wordpress",
                [__CLASS__, "render"],
                $icon,
                77
            );

            self::registerSubMenus([
                '' => 'Settings',
                'modules' => 'Modules',
            ]);
        });
        add_filter('print_styles_array', [__CLASS__, 'filterAdminStylesheets']);
        add_action('current_screen', [__CLASS__, 'loadResources']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'overrideAdminStyles']);
    }

    public static function render()
    {
        ?>
        <div id="inertia-wordpress__admin-page--wrapper" class="v-cloak font-sans"></div>
<?php
    }

    public static function get_icon_uri()
    {
        return 'data:image/svg+xml;base64,' . self::$baseIcon;
    }

    public static function registerSubMenus(array $items)
    {
        foreach ($items as $key => $value) {
            $url = !empty($key) ? '#' . $key : '';

            add_submenu_page(
                'inertia-wordpress',
                $value,
                $value,
                'manage_options',
                'inertia-wordpress' . $url,
                [__CLASS__, "render"],
            );
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
            return array_filter($stylesArray, fn ($style) => in_array($style, ['forms']) === false);
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

    public static function isInertiaMenu()
    {
        if (!is_admin() || function_exists('get_current_screen') === false) {
            return false;
        }
        $screen = get_current_screen();

        if ($screen->base !== "toplevel_page_inertia-wordpress") {
            return false;
        } else {
            return true;
        }
    }
}
