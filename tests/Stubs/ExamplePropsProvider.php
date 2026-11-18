<?php

namespace EvoMark\InertiaWordpress\Tests\Stubs;

use EvoMark\InertiaWordpress\Contracts\ProvidesInertiaProperties;
use EvoMark\InertiaWordpress\Data\RenderContext;

final class ExamplePropsProvider implements ProvidesInertiaProperties
{
    /**
     * @param iterable<string, mixed> $properties
     */
    public function __construct(private iterable $properties)
    {
    }

    public function toInertiaProperties(RenderContext $context): iterable
    {
        return $this->properties;
    }
}
