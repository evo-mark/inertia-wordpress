<?php

namespace EvoMark\InertiaWordpress\Tests\Support;

use EvoMark\InertiaWordpress\Contracts\ResponseEmitter;
use EvoMark\InertiaWordpress\Data\HttpResponse;

/**
 * Records emitted responses instead of sending headers and exiting.
 */
final class FakeEmitter implements ResponseEmitter
{
    /** @var array<int, HttpResponse> */
    public array $emitted = [];

    public ?HttpResponse $last = null;

    public function emit(HttpResponse $response): void
    {
        $this->emitted[] = $response;
        $this->last = $response;

        throw new ResponseEmitted();
    }
}
