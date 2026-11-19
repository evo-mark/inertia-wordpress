<?php

namespace EvoMark\InertiaWordpress\Contracts;

use BackedEnum;
use DateInterval;
use DateTimeInterface;
use UnitEnum;

/**
 * A prop the client caches after it is first loaded. On later visits the client reports
 * it in the X-Inertia-Except-Once-Props header and the server skips resolving it.
 */
interface Onceable
{
    public function once(bool $value = true): static;

    public function shouldResolveOnce(): bool;

    /**
     * Whether to resolve and send the prop even if the client already has it.
     */
    public function shouldBeRefreshed(): bool;

    /**
     * The cache key the client stores the prop under, or null to use the prop name.
     */
    public function getKey(): ?string;

    public function as(BackedEnum|UnitEnum|string $key): static;

    public function until(DateTimeInterface|DateInterval|int $delay): static;

    /**
     * When the client's cached copy expires, as a Unix timestamp in milliseconds.
     */
    public function expiresAt(): ?int;
}
