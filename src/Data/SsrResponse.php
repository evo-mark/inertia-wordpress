<?php

namespace EvoMark\InertiaWordpress\Data;

class SsrResponse
{
    /**
     * @var array
     */
    public array $head;

    /**
     * @var string
     */
    public string $body;

    /**
     * Prepare the Inertia Server Side Rendering (SSR) response.
     */
    public function __construct(array $head, string $body)
    {
        $this->head = $head;
        $this->body = $body;
    }
}
