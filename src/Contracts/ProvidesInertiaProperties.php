<?php

namespace EvoMark\InertiaWordpress\Contracts;

use EvoMark\InertiaWordpress\Data\RenderContext;

/**
 * An object that contributes several top-level props at once. Add it to the props (or
 * shared props) without a key, and each key it returns becomes a normal prop.
 */
interface ProvidesInertiaProperties
{
    /**
     * Called on each render, before partial reloads are filtered: return closures or
     * deferred props for anything expensive.
     *
     * @return iterable<string, mixed>
     */
    public function toInertiaProperties(RenderContext $context): iterable;
}
