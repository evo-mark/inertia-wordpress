<?php

namespace EvoMark\InertiaWordpress\Modules;

use EvoMark\InertiaWordpress\Container;
use Illuminate\Support\Arr;

class ModuleSetup
{
    /**
     * @hook init
     */
    public static function init()
    {
        $container = Container::getInstance();
        $container->set('modules.acf', new \EvoMark\InertiaWordpress\Modules\AdvancedCustomFields\Module());
        /*
        ContactForm7\Activate::run();
        SmartCrawl\Activate::run();
        ScwMeilisearch\Activate::run(); */
    }

    /**
     * Given a single, or array of potential plugin entry files, checks if any of them are active
     */
    public static function checkActive(array | string $entry): bool
    {
        $entry = Arr::wrap($entry);

        foreach ($entry as $file) {
            if (self::isEntryFile($file)) return true;
        }

        return false;
    }

    /**
     * Is the given entry file an active plugin?
     *
     * @param string $file The entry file to check
     */
    public static function isEntryFile(string $file): bool
    {
        return in_array($file, apply_filters('active_plugins', get_option('active_plugins')));
    }
}
