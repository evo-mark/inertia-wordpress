<?php

namespace EvoMark\InertiaWordpress\Traits;

use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTimeInterface;
use UnitEnum;

trait ResolvesOnce
{
    protected bool $once = false;

    protected bool $refresh = false;

    /** Seconds until the client's cached copy expires, or null for no expiry. */
    protected ?int $ttl = null;

    protected ?string $key = null;

    /**
     * Only resolve this prop once: the client caches it and skips it on later visits.
     *
     * @param  string|null  $as  A custom cache key (see as())
     * @param  DateTimeInterface|DateInterval|int|null  $until  When the cached copy expires (see until())
     */
    public function once(bool $value = true, ?string $as = null, DateTimeInterface|DateInterval|int|null $until = null): static
    {
        $this->once = $value;

        if ($as !== null) {
            $this->as($as);
        }

        if ($until !== null) {
            $this->until($until);
        }

        return $this;
    }

    public function shouldResolveOnce(): bool
    {
        return $this->once;
    }

    public function shouldBeRefreshed(): bool
    {
        return $this->refresh;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Cache the prop under a custom key, e.g. to share one cached value between pages.
     */
    public function as(BackedEnum|UnitEnum|string $key): static
    {
        $this->key = match (true) {
            $key instanceof BackedEnum => (string) $key->value,
            $key instanceof UnitEnum => $key->name,
            default => $key,
        };

        return $this;
    }

    /**
     * Resolve and send the prop on this visit even if the client already has it.
     */
    public function fresh(bool $value = true): static
    {
        $this->refresh = $value;

        return $this;
    }

    /**
     * Expire the client's cached copy after a number of seconds, an interval, or at a date.
     */
    public function until(DateTimeInterface|DateInterval|int $delay): static
    {
        $this->ttl = match (true) {
            $delay instanceof DateTimeInterface => max(0, $delay->getTimestamp() - Carbon::now()->getTimestamp()),
            $delay instanceof DateInterval => (int) CarbonInterval::instance($delay)->totalSeconds,
            default => $delay,
        };

        return $this;
    }

    public function expiresAt(): ?int
    {
        if ($this->ttl === null) {
            return null;
        }

        return Carbon::now()->addSeconds($this->ttl)->getTimestamp() * 1000;
    }
}
