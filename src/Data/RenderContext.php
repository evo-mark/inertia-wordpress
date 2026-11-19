<?php

namespace EvoMark\InertiaWordpress\Data;

use EvoMark\InertiaWordpress\Request\RequestHandler;

/**
 * What is being rendered, passed to ProvidesInertiaProperties objects.
 */
class RenderContext
{
    public function __construct(
        public readonly string $component,
        public readonly RequestHandler $request,
    ) {
    }
}
