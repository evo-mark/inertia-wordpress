<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Tests\Stubs\EncryptedTestController;
use EvoMark\InertiaWordpress\Tests\Stubs\TestController;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/HistoryTest.php
 * @see https://inertiajs.com/history-encryption
 */
class HistoryTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function render(): array
    {
        $request = $this->currentRequest();
        $request->setComponent('User/Edit');

        return $request->getPageData();
    }

    public function test_the_history_is_not_encrypted_or_cleared_by_default(): void
    {
        $this->makeInertiaRequest('/');

        $page = $this->render();

        $this->assertFalse($page['encryptHistory']);
        $this->assertFalse($page['clearHistory']);
    }

    public function test_the_history_can_be_encrypted(): void
    {
        $this->makeInertiaRequest('/');
        Inertia::encryptHistory();

        $this->assertTrue($this->render()['encryptHistory']);
    }

    public function test_the_history_can_be_encrypted_via_the_controller(): void
    {
        $this->makeInertiaRequest('/');

        $response = $this->captureResponse(fn () => (new EncryptedTestController())->handle());

        $this->assertTrue($response->getBody()['encryptHistory']);
    }

    public function test_controllers_do_not_encrypt_history_by_default(): void
    {
        $this->makeInertiaRequest('/');

        $response = $this->captureResponse(fn () => (new TestController())->handle());

        $this->assertFalse($response->getBody()['encryptHistory']);
    }

    public function test_the_history_can_be_encrypted_globally(): void
    {
        $this->setSetting('history_encrypt', true);
        $this->makeInertiaRequest('/');

        $this->assertTrue($this->render()['encryptHistory']);
    }

    public function test_the_history_can_be_encrypted_globally_and_overridden(): void
    {
        $this->setSetting('history_encrypt', true);
        $this->makeInertiaRequest('/');
        Inertia::encryptHistory(false);

        $this->assertFalse($this->render()['encryptHistory']);
    }

    public function test_the_history_can_be_cleared(): void
    {
        $this->makeInertiaRequest('/');
        Inertia::clearHistory();

        $this->assertTrue($this->render()['clearHistory']);
    }

    public function test_the_history_can_be_cleared_when_redirecting(): void
    {
        $this->makeInertiaRequest('/', 'POST');
        Inertia::clearHistory();

        // The browser follows the redirect to a new request.
        $this->makeInertiaRequest('/users');

        $this->assertTrue($this->render()['clearHistory']);
    }

    public function test_clearing_history_only_applies_to_the_next_page(): void
    {
        $this->makeInertiaRequest('/', 'POST');
        Inertia::clearHistory();

        $this->makeInertiaRequest('/users');
        $this->assertTrue($this->render()['clearHistory']);

        $this->makeInertiaRequest('/users/1');
        $this->assertFalse($this->render()['clearHistory']);
    }
}
