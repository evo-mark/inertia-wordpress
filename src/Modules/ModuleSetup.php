<?php

namespace EvoMark\InertiaWordpress\Modules;

use Illuminate\Support\Arr;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Helpers\HookActions;
use EvoMark\InertiaWordpress\Modules\AdvancedCustomFields\Module as AdvancedCustomFieldsModule;
use EvoMark\InertiaWordpress\Modules\ContactForm7\Module as ContactForm7Module;
use EvoMark\InertiaWordpress\Modules\NinjaForms\Module as NinjaFormsModule;
use EvoMark\InertiaWordpress\Modules\TheSeoFramework\Module as TheSeoFrameworkModule;
use EvoMark\InertiaWordpress\Modules\WooCommerce\Module as WooCommerceModule;
use EvoMark\InertiaWordpress\Modules\WebPExpress\Module as WebPExpressModule;

class ModuleSetup
{
    protected static array $registeredModules = [];

    /**
     * @hook plugins_loaded
     */
    public static function init()
    {
        $container = Container::getInstance();
        $container->set('modules', collect());

        Inertia::addModule(AdvancedCustomFieldsModule::class);
        Inertia::addModule(ContactForm7Module::class);
        Inertia::addModule(NinjaFormsModule::class);
        Inertia::addModule(WebPExpressModule::class);
        Inertia::addModule(TheSeoFrameworkModule::class);
        Inertia::addModule(WooCommerceModule::class);


        /**
         * Hook for registering new Inertia Wordpress modules
         *
         * @since 0.4.0
         *
         * @param string $module The class string for the module, must extend 'EvoMark\InertiaWordpress\Modules\BaseModule'
         */
        do_action(HookActions::MODULES);

        self::registerModules();

        add_action(HookActions::SET_GLOBAL_SHARES, [__CLASS__, 'bootModules']);
    }

    /**
     * Given a single, or array of potential plugin entry files, checks if any of them are active
     */
    public static function checkActive(array | string $entry): bool
    {
        $entry = Arr::wrap($entry);

        foreach ($entry as $file) {
            if (self::isEntryFile($file)) {
                return true;
            }
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

    public static function registerModules()
    {
        $container = Container::getInstance();
        $modules = $container->get('modules');

        foreach ($modules as $module) {
            $instance = $module::create();
            if (method_exists($instance, 'init')) {
                $instance->init();
            }
            if (! $instance->isEnabled()) {
                continue;
            }
            self::$registeredModules[] = $instance;
            $instance->register();
        }
    }

    /**
     * @hook HookActions::SET_GLOBAL_SHARES
     */
    public static function bootModules()
    {
        foreach (self::$registeredModules as $module) {
            if ($module->isEnabled()) {
                $module->boot();
            }
        }
    }
}
