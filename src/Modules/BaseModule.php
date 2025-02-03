<?php

namespace EvoMark\InertiaWordpress\Modules;

use EvoMark\InertiaWordpress\Helpers\Settings;

abstract class BaseModule
{
    protected string $title;
    protected string $logo = "";
    protected string $slug;
    protected string $class;
    protected array|string $entry;
    protected bool $isInternal = false;

    protected function __construct()
    {
        $child = array_slice(explode("\\", get_called_class()), -2, 1)[0] ?? "Unknown";
        if (empty($this->class)) {
            throw new \Exception('No property $class declared in ' . $child . ' module');
        } elseif (empty($this->entry)) {
            throw new \Exception('No property $entry declared in ' . $child . ' module');
        } elseif (empty($this->slug)) {
            throw new \Exception('No property $slug declared in ' . $child . ' module');
        } elseif (empty($this->title)) {
            throw new \Exception('No property $title declared in ' . $child . ' module');
        }
    }

    public static function create()
    {
        return new static();
    }

    /**
     * The init function is called when the class is instantiated, regardless
     * of whether or not the module is enabled.
     * You should use this function to provide any setup that needs to run
     * no matter what. It should never be used to modify frontend output
     */
    public function init()
    {
    }

    /**
     * The register function is called before a module instance is created.
     * You should only use this to register essential side-effects using
     * Wordpress hooks.
     */
    public function register()
    {
    }


    /**
     * Called when Inertia is ready to send a response
     */
    abstract public function boot(): void;

    /**
     * Check if the module is enabled and its plugin is available and active
     */
    public function isEnabled()
    {
        $enabledModules = Settings::get('modules');
        if (empty($enabledModules)) {
            return false;
        }
        
        return (class_exists($this->class) || function_exists($this->class)) &&
            ModuleSetup::checkActive($this->entry) === true &&
            in_array($this->slug, $enabledModules);
    }

    public function getData(): array
    {
        return [
            'title' => $this->title,
            'logo' => $this->logo,
            'slug' => $this->slug,
            'class' => $this->class,
            'entry' => $this->entry,
            'isInternal' => $this->isInternal,
        ];
    }
}
