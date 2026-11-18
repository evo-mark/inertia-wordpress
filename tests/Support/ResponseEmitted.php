<?php

namespace EvoMark\InertiaWordpress\Tests\Support;

use RuntimeException;

/**
 * Thrown by FakeEmitter to halt execution where production code would exit().
 */
final class ResponseEmitted extends RuntimeException
{
}
