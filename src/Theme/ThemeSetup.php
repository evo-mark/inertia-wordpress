<?php

namespace EvoMark\InertiaWordpress\Theme;

use EvoMark\WpVite\WpVite;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Theme\Utils;
use EvoMark\InertiaWordpress\Helpers\Path;
use EvoMark\InertiaWordpress\Helpers\Settings;

class ThemeSetup
{
    public static function init()
    {
        add_filter('template_include', [__CLASS__, 'handleTemplateInclude']);
        self::addTemplateDirectories();
        self::enqueueScripts();
        self::getThemeVersion();
    }

    public static function enqueueScripts()
    {
        $entryFile = Settings::get('entry_file');
        $entryNamespace = Settings::get('entry_namespace');

        $vite = new WpVite();
        $vite->enqueue([
            'input' => $entryFile,
            'namespace' => $entryNamespace
        ]);
    }

    public static function getThemeVersion()
    {
        $entryNamespace = Settings::get('entry_namespace');
        $viteDir =  Path::join(wp_upload_dir()['basedir'], 'scw-vite-hmr', $entryNamespace);
        $container = Container::getInstance();
        $request = $container->get('requestHandler');

        if (file_exists(Path::join($viteDir, 'hot'))) {
            $request->setVersion("dev");
        } else {
            $request->setVersion(md5_file(Path::join($viteDir, 'build', 'manifest.json')));
        }
    }

    public static function handleTemplateInclude($template)
    {
        $templateName = basename($template);
        $controllerDir = get_stylesheet_directory() . '/controllers';
        $controllerFile = $controllerDir . '/' . $templateName;

        if (file_exists($controllerFile)) {
            $class = Utils::getClass($template);
            if (in_array('EvoMark\InertiaWordpress\Contracts\InertiaControllerContract', class_implements($class)) === false) {
                return $template;
            }

            $controller = new $class();
            echo $controller->handle();
        } else return $template;
    }

    /**
     * Add candidates in different folders for our template resolution
     *
     * Returns an array of templates in descending order of priority
     */
    public static function addTemplateDirectories($templateBases = null)
    {
        $templateBases ??= self::getDefaultTemplateBases();

        array_map(function ($type) {
            add_filter("{$type}_template_hierarchy", function ($templates) use ($type) {

                $directories = ['controllers'];

                foreach ($templates as $key => $filename) {
                    $templates[$key] = [$filename];

                    foreach ($directories as $directory) {
                        array_unshift($templates[$key], $directory . DIRECTORY_SEPARATOR . $filename);
                    }
                }


                return self::arrayFlatten($templates);
            });
        }, $templateBases);
    }

    public static function getDefaultTemplateBases(): array
    {
        return [
            '404',
            'archive',
            'attachment',
            'author',
            'category',
            'date',
            'embed',
            'frontpage',
            'home',
            'index',
            'page',
            'paged',
            'privacypolicy',
            'search',
            'single',
            'singular',
            'tag',
            'taxonomy',
        ];
    }

    public static function arrayFlatten(array $array): array
    {
        $result = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                // Recursively flatten the array
                $result = array_merge($result, self::arrayFlatten($item));
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }
}
