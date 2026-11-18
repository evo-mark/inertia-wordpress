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

        // Check the asset version before anything is shared: sharing reads (and deletes)
        // flashed data and validation errors, which must survive the 409 + reload.
        if ($request->hasVersionMismatch()) {
            return InertiaResponse::handleVersionChange();
        }

        if ($this->encryptHistory === true) {
            $request->setEncryptHistory();
        }
        $request->setComponent($component);
        $request->setProps($props);
        $request->setGlobalShares();

        if ($request->isInertia()) {
            return InertiaResponse::handle();
        }

        $view = Settings::get('root_template');

        // Not require_once: the template must render on every call, e.g. repeated renders in WP-CLI.
        require Path::join(get_stylesheet_directory(), $view);
    }
}
