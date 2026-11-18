<?php

namespace EvoMark\InertiaWordpress\Tests\Stubs;

use EvoMark\InertiaWordpress\InertiaController;

/**
 * Concrete controller for exercising InertiaController::render().
 */
class TestController extends InertiaController
{
    /**
     * @param array<array-key, mixed>|object $props
     */
    public function __construct(
        private string $component = 'User/Edit',
        private array|object $props = [],
    ) {
    }

    public function handle()
    {
        return $this->render($this->component, $this->props);
    }
}
