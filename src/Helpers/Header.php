<?php

namespace EvoMark\InertiaWordpress\Helpers;

class Header
{
    public const INERTIA = 'x-inertia';

    public const ERROR_BAG = 'x-inertia-error-bag';

    public const LOCATION = 'x-inertia-location';

    public const VERSION = 'x-inertia-version';

    public const PARTIAL_COMPONENT = 'x-inertia-partial-component';

    public const PARTIAL_ONLY = 'x-inertia-partial-data';

    public const PARTIAL_EXCEPT = 'x-inertia-partial-except';

    public const RESET = 'x-inertia-reset';

    /** Once props the client already has cached, which the server can skip. */
    public const EXCEPT_ONCE_PROPS = 'x-inertia-except-once-props';

    /** Whether infinite scroll items should be appended ("append") or prepended ("prepend"). */
    public const INFINITE_SCROLL_MERGE_INTENT = 'x-inertia-infinite-scroll-merge-intent';
}
