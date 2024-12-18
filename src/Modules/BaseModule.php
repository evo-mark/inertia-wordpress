<?php

namespace EvoMark\InertiaWordpress\Modules;

use EvoMark\InertiaWordpress\Helpers\Settings;
use EvoMark\InertiaWordpress\Modules\ModuleSetup;

abstract class BaseModule
{
    protected string $class;
    protected string $slug;
    protected array|string $entry;

    protected function __construct()
    {
        $child = array_slice(explode("\\", get_called_class()), -2, 1)[0] ?? "Unknown";
        if (empty($this->class)) {
            throw new \Exception('No plugin $class declared in ' . $child . ' module');
        } else if (empty($this->entry)) {
            throw new \Exception('No plugin $entry declared in ' . $child . ' module');
        } else if (empty($this->slug)) {
            throw new \Exception('No plugin $slug declared in ' . $child . ' module');
        }
    }

    public function isEnabled()
    {
        $enabledModules = Settings::get('modules');
        return class_exists($this->class) && ModuleSetup::checkActive($this->entry) === true && in_array($this->slug, $enabledModules);
    }
}
