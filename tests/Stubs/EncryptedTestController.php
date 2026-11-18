<?php

namespace EvoMark\InertiaWordpress\Tests\Stubs;

/**
 * The WordPress analogue of Laravel's EncryptHistoryMiddleware.
 */
class EncryptedTestController extends TestController
{
    protected bool $encryptHistory = true;
}
