<?php

namespace EvoMark\InertiaWordpress\Contracts;

use EvoMark\InertiaWordpress\Data\HttpResponse;

/**
 * Sends an HttpResponse to the client. Implementations are expected to end the request
 * once the response is sent (the production emitter exits).
 */
interface ResponseEmitter
{
    public function emit(HttpResponse $response): void;
}
