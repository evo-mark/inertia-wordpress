<?php

namespace EvoMark\InertiaWordpress\Tests\Stubs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final class ArrayableValue implements Arrayable
{
    /**
     * @param array<string, mixed> $value
     */
    public function __construct(private array $value)
    {
    }

    public function toArray(): array
    {
        return $this->value;
    }
}
