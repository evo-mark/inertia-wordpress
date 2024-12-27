<?php

namespace EvoMark\InertiaWordpress;

use WP_CLI;
use Exception;
use WP_REST_Request;
use EvoWpRestRegistration\RestApi;
use EvoMark\InertiaWordpress\Helpers\Path;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Data\MessageBag;
use EvoMark\InertiaWordpress\Helpers\Settings;
use EvoMark\InertiaWordpress\Theme\ThemeSetup;
use EvoMark\InertiaWordpress\Modules\ModuleSetup;
use YahnisElsts\PluginUpdateChecker\v5p5\Vcs\Api;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use EvoMark\InertiaWordpress\Request\RequestHandler;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Helpers\Plugin as HelpersPlugin;

class Plugin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'adminInit']);
        add_action('rest_api_init', [$this, 'restApiInit']);
    }

    /**
     * Setup function for configuring the plugin environment
     * @hook none
     */
    public function setup(string $entryFile)
    {
        $container = Container::getInstance();
        $container->set('env.entry', $entryFile);
        $container->set('env.root', plugin_dir_path($entryFile));
        $container->set('env.baseUrl', plugin_dir_url($entryFile));
        $container->set('requestHandler', \Di\create(RequestHandler::class));
        HelpersPlugin::setPluginVersion();

        $uploadDir = wp_upload_dir()['basedir'];
        $container->set('env.uploads', Path::join($uploadDir, "inertia"));
        if (! wp_mkdir_p($container->get('env.uploads'))) {
            throw new Exception("[Inertia Wordpress] Unable to create uploads folder");
        }
    }

    /**
     * @hook init
     */
    public function init()
    {
        $this->registerCommands();
        $this->registerSettings();
        $this->checkForUpdates();
        $this->registerRest();
        $this->registerRestErrorHandler();
        ThemeSetup::init();
        ModuleSetup::init();
    }

    /**
     * @hook admin_init
     */
    public function adminInit()
    {
        $this->registerThemeTemplateFilters();
    }

    /**
     * @hook rest_api_init
     */
    public function restApiInit()
    {
        $this->registerThemeTemplateFilters();
    }

    private function registerCommands()
    {
        /** @disregard P1011 WP_CLI might not be available  */
        if (defined('WP_CLI') && \WP_CLI) {
            WP_CLI::add_command('inertia:start-ssr', \EvoMark\InertiaWordpress\Commands\StartSsrCommand::class);
            WP_CLI::add_command('inertia:stop-ssr', \EvoMark\InertiaWordpress\Commands\StopSsrCommand::class);
            WP_CLI::add_command('inertia:create-theme', \EvoMark\InertiaWordpress\Commands\CreateThemeCommand::class);
        };
    }

    private function registerSettings()
    {
        Settings::registerPage();

        register_setting('inertia', 'inertia_ssr_enabled', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'boolval',
            'label' => 'Enable the SSR functionality',
        ]);

        register_setting('inertia', 'inertia_ssr_url', [
            'type' => 'string',
            'default' => 'http://127.0.0.1:13714',
            'label' => 'The URL to use for the SSR service',
        ]);

        register_setting('inertia', 'inertia_history_encrypt', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'boolval',
            'label' => 'Encrypt the stored history data',
        ]);

        register_setting('inertia', 'inertia_root_template', [
            'type' => 'string',
            'default' => 'app.php',
            'label' => 'The root template file in your theme',
        ]);

        register_setting('inertia', 'inertia_entry_file', [
            'type' => 'string',
            'default' => 'resources/js/main.js',
            'label' => 'The JS entry file',
        ]);

        register_setting('inertia', 'inertia_entry_namespace', [
            'type' => 'string',
            'default' => 'theme-inertia',
            'label' => 'The JS entry namespace',
        ]);

        register_setting('inertia', 'inertia_modules', [
            'type' => 'array',
            'default' => [],
            'label' => 'Inertia modules that are enabled',
        ]);

        register_setting('inertia', 'inertia_templates_directory', [
            'type' => 'string',
            'default' => 'resources/js/templates',
            'label' => 'Theme templates directory',
        ]);
    }

    /**
     * Check for plugin updates from the official repo
     */
    private function checkForUpdates()
    {
        $container = Container::getInstance();
        $checker = PucFactory::buildUpdateChecker(
            'https://github.com/evo-mark/inertia-wordpress/',
            $container->get('env.entry'),
            'inertia-wordpress'
        );
        /** @disregard P1013 Is a valid function */
        $checker->getVcsApi()->enableReleaseAssets('/\.zip($|[?&#])/i', Api::REQUIRE_RELEASE_ASSETS);
        dd((array) $checker->requestInfo());
    }

    private function registerRest()
    {
        new RestApi([
            'namespace' => 'EvoMark\\InertiaWordpress\\RestApi\\',
            'version' => 1,
            'directory' => __DIR__ . '/RestApi',
            'base_url' => 'inertia-wordpress',
        ]);
    }

    private function registerRestErrorHandler()
    {
        add_filter('rest_request_after_callbacks', [$this, 'handleRestError'], PHP_INT_MAX, 3);
    }

    public function handleRestError($response, $handler, WP_REST_Request $request)
    {
        if (is_wp_error($response) && $request->get_header(Header::INERTIA) === "true") {
            /** @var \WP_Error $response */
            $bag = $request->get_header(Header::ERROR_BAG) ?? "default";
            RequestResponse::setFlashData('errors', [
                $bag => new MessageBag(RequestResponse::formatErrors($response->error_data['rest_invalid_param']['params'] ?? [])),
            ]);
            return Inertia::back();
        }

        return $response;
    }

    public function registerThemeTemplateFilters()
    {
        $postTypes = [...array_values(get_post_types([
            'public' => true,
            '_builtin' => false,
        ])), 'post', 'page'];
        foreach ($postTypes as $type) {
            add_filter('theme_' . $type . '_templates', [$this, 'registerLayoutsAsTemplates'], 11, 3);
        }

        add_filter('default_page_template_title', function () {
            return __('Inherit Page Template', 'inertia-wordpress');
        });
    }

    public function registerLayoutsAsTemplates($pageTemplates, $theme, $post)
    {
        $templatesDirectory = Settings::get('templates_directory');
        if (substr($templatesDirectory, 0, 1) !== '/') {
            $templatesDirectory = '/' . $templatesDirectory;
        }
        $templatesDirectory = get_stylesheet_directory() . $templatesDirectory;

        if (! file_exists($templatesDirectory)) {
            return $pageTemplates;
        }

        $layouts = opendir($templatesDirectory);
        while (($entry = readdir($layouts)) !== false) {
            if (!preg_match("/^\./", $entry)) {
                $filename = $entry;
                $label = pathinfo($filename)['filename'];
                $pageTemplates[$filename] = ucwords($label);
            }
        }
        closedir($layouts);

        return $pageTemplates;
    }
}
