<?php

namespace EvoMark\InertiaWordpress;

use EvoMark\InertiaWordpress\Helpers\Path;
use EvoMark\InertiaWordpress\Helpers\Settings;
use EvoMark\InertiaWordpress\Contracts\InertiaControllerContract;
use EvoMark\InertiaWordpress\Responses\InertiaResponse;

abstract class InertiaController implements InertiaControllerContract
{
    protected bool $encryptHistory = false;

    public function render(string $component, array $props = [])
    {
        $request = inertia_request();
        if ($this->encryptHistory === true) {
            $request->setEncryptHistory();
        }
        $request->setComponent($component);
        $request->setProps($props);
        $request->setGlobalShares();

        if ($request->isInertia()) return InertiaResponse::handle();

        $view = Settings::get('root_template');

        require_once Path::join(get_stylesheet_directory(), $view);
    }
}
