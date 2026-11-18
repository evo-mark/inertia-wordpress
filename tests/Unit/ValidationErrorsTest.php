<?php

namespace EvoMark\InertiaWordpress\Tests\Unit;

use EvoMark\InertiaWordpress\Data\MessageBag;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Tests\TestCase;

/**
 * Errors are flashed (to a transient) by the REST error handler and shared on the next visit.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/MiddlewareTest.php
 * @see https://inertiajs.com/validation
 */
class ValidationErrorsTest extends TestCase
{
    /**
     * @param array<string, MessageBag> $bags
     */
    private function flashErrors(array $bags): void
    {
        RequestResponse::setFlashData('errors', $bags);
    }

    private function resolvedErrors(): mixed
    {
        $this->currentRequest()->setGlobalShares();

        return Inertia::getShared('errors')();
    }

    public function test_validation_errors_are_registered_as_an_always_prop(): void
    {
        $this->currentRequest()->setGlobalShares();

        $this->assertInstanceOf(AlwaysProp::class, Inertia::getShared('errors'));
    }

    public function test_validation_errors_can_be_empty(): void
    {
        $errors = $this->resolvedErrors();

        $this->assertIsObject($errors);
        $this->assertEmpty(get_object_vars($errors));
    }

    public function test_empty_validation_errors_serialize_to_an_empty_json_object(): void
    {
        $this->currentRequest()->setGlobalShares();
        $this->currentRequest()->setComponent('User/Edit');

        $json = json_encode($this->currentRequest()->getPageData()['props']);

        $this->assertStringContainsString('"errors":{}', $json);
    }

    public function test_validation_errors_are_mapped_to_strings_by_default(): void
    {
        $this->flashErrors(['default' => new MessageBag([
            'name' => ['The name field is required.'],
            'email' => ['Not a valid email address.', 'Another email error.'],
        ])]);

        $errors = $this->resolvedErrors();

        $this->assertIsObject($errors);
        $this->assertSame('The name field is required.', $errors->name);
        $this->assertSame('Not a valid email address.', $errors->email);
    }

    public function test_validation_errors_with_named_error_bags_are_scoped(): void
    {
        $this->flashErrors(['example' => new MessageBag([
            'name' => ['The name field is required.'],
            'email' => ['Not a valid email address.'],
        ])]);

        $errors = $this->resolvedErrors();

        $this->assertIsObject($errors);
        $this->assertSame('The name field is required.', $errors->example->name);
        $this->assertSame('Not a valid email address.', $errors->example->email);
    }

    public function test_validation_errors_are_scoped_to_error_bag_header(): void
    {
        $this->makeInertiaRequest('/', 'GET', ['X-Inertia-Error-Bag' => 'example']);
        $this->flashErrors(['default' => new MessageBag([
            'name' => ['The name field is required.'],
            'email' => ['Not a valid email address.'],
        ])]);

        $errors = $this->resolvedErrors();

        $this->assertIsObject($errors);
        $this->assertSame('The name field is required.', $errors->example->name);
        $this->assertSame('Not a valid email address.', $errors->example->email);
    }

    public function test_validation_errors_are_only_shared_once(): void
    {
        $this->flashErrors(['default' => new MessageBag([
            'name' => ['The name field is required.'],
        ])]);
        $this->assertSame('The name field is required.', $this->resolvedErrors()->name);

        $this->makeRequest('/');

        $this->assertEmpty(get_object_vars($this->resolvedErrors()));
    }

    public function test_default_validation_errors_can_be_overwritten(): void
    {
        $this->flashErrors(['example' => new MessageBag([
            'name' => ['The name field is required.'],
        ])]);

        $request = $this->makeInertiaRequest('/');
        $request->setGlobalShares();
        $request->setComponent('User/Edit');
        $request->setProps(['errors' => 'foo']);

        $this->assertSame('foo', $request->getPageData()['props']['errors']);
    }

    public function test_validation_errors_are_included_on_partial_reloads(): void
    {
        $request = $this->makeInertiaRequest('/', 'GET', [
            'X-Inertia-Partial-Component' => 'User/Edit',
            'X-Inertia-Partial-Data' => 'user',
        ]);
        $this->flashErrors(['default' => new MessageBag([
            'name' => ['The name field is required.'],
        ])]);
        $request->setGlobalShares();
        $request->setComponent('User/Edit');
        $request->setProps(['user' => 'Jonathan', 'other' => 'value']);

        $props = $request->getPageData()['props'];

        $this->assertSame('Jonathan', $props['user']);
        $this->assertArrayNotHasKey('other', $props);
        $this->assertSame('The name field is required.', $props['errors']->name);
    }
}
