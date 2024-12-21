<?php

namespace EvoMark\InertiaWordpress\Modules;

use EvoMark\InertiaWordpress\Helpers\Settings;

abstract class BaseModule
{
    protected string $slug;
    protected string $class;
    protected array|string $entry;

    protected function __construct()
    {
        $child = array_slice(explode("\\", get_called_class()), -2, 1)[0] ?? "Unknown";
        if (empty($this->class)) {
            throw new \Exception('No plugin $class declared in ' . $child . ' module');
        } elseif (empty($this->entry)) {
            throw new \Exception('No plugin $entry declared in ' . $child . ' module');
        } elseif (empty($this->slug)) {
            throw new \Exception('No plugin $slug declared in ' . $child . ' module');
        }

        if ($this->isEnabled()) {
            $this->init();
        }
    }

    public static function boot()
    {
        return new static;
    }

    /**
     * Called when the module is confirmed to be enabled
     */
    abstract public function init(): void;

    /**
     * Check if the module is enabled and its plugin is available and active
     */
    public function isEnabled()
    {
        $enabledModules = Settings::get('modules');
        return class_exists($this->class) &&
            ModuleSetup::checkActive($this->entry) === true &&
            in_array($this->slug, $enabledModules);
    }
}
