<?php

namespace EvoMark\InertiaWordpress\Props;

use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\Deferrable;
use EvoMark\InertiaWordpress\Data\ScrollMetadata;
use EvoMark\InertiaWordpress\Traits\MergesProps;
use EvoMark\InertiaWordpress\Request\RequestHandler;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;
use EvoMark\InertiaWordpress\Contracts\ProvidesScrollMetadata;

/**
 * A paginated prop for the client's <InfiniteScroll> component. Each page of items is
 * merged into the list under the wrapper key, and pagination details are sent as metadata.
 */
class ScrollProp implements Deferrable, Mergeable
{
    use MergesProps;
    use ResolvesCallables;

    protected mixed $value;

    protected mixed $resolved = null;

    protected bool $isResolved = false;

    protected string $wrapper;

    /** @var ProvidesScrollMetadata|callable|null */
    protected $metadata;

    protected bool $deferred = false;

    protected ?string $deferGroup = null;

    /**
     * @param  mixed  $value  The page of results (or a closure returning it), e.g. ['data' => [...]]
     * @param  string  $wrapper  The key holding the list of items to merge
     * @param  ProvidesScrollMetadata|callable|null  $metadata  Pagination details, a callback given the
     *         resolved value that returns them, or null to read them from the main WP_Query
     */
    public function __construct(mixed $value, string $wrapper = 'data', ProvidesScrollMetadata|callable|null $metadata = null)
    {
        $this->merge = true;
        $this->value = $value;
        $this->wrapper = $wrapper;
        $this->metadata = $metadata;

        // Merge into the wrapped list, not the whole prop, so pagination details are replaced.
        $this->appendsAtPaths = [$wrapper];
    }

    /**
     * Merge new items at the start or end of the list, as asked for by the client
     * (scrolling up loads earlier items, so they're prepended).
     */
    public function configureMergeIntent(?RequestHandler $request = null): static
    {
        $request ??= inertia_request();
        $prepend = $request->getHeader(Header::INFINITE_SCROLL_MERGE_INTENT) === 'prepend';

        // Set rather than add, so configuring twice (e.g. for SSR and again for the page) is harmless.
        $this->appendsAtPaths = $prepend ? [] : [$this->wrapper];
        $this->prependsAtPaths = $prepend ? [$this->wrapper] : [];

        return $this;
    }

    /**
     * Leave the prop out of the first visit and fetch it straight afterwards.
     */
    public function defer(?string $group = null): static
    {
        $this->deferred = true;
        $this->deferGroup = $group;

        return $this;
    }

    public function shouldDefer(): bool
    {
        return $this->deferred;
    }

    public function group()
    {
        return $this->deferGroup ?? 'default';
    }

    /**
     * @return array{pageName: string, previousPage: int|string|null, nextPage: int|string|null, currentPage: int|string|null}
     */
    public function metadata(): array
    {
        $provider = $this->resolveMetadataProvider();

        return [
            'pageName' => $provider->getPageName(),
            'previousPage' => $provider->getPreviousPage(),
            'nextPage' => $provider->getNextPage(),
            'currentPage' => $provider->getCurrentPage(),
        ];
    }

    protected function resolveMetadataProvider(): ProvidesScrollMetadata
    {
        if ($this->metadata instanceof ProvidesScrollMetadata) {
            return $this->metadata;
        }

        $value = $this();

        if ($this->metadata === null) {
            // A WP_Query value describes itself; otherwise use the main query, as on archives.
            return ScrollMetadata::fromQuery($value instanceof \WP_Query ? $value : ($GLOBALS['wp_query'] ?? null));
        }

        return call_user_func($this->metadata, $value);
    }

    /**
     * Resolved once, as both the prop value and its metadata need it.
     */
    public function __invoke()
    {
        if (! $this->isResolved) {
            $this->resolved = $this->resolveCallable($this->value);
            $this->isResolved = true;
        }

        return $this->resolved;
    }
}
