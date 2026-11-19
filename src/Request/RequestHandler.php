<?php

namespace EvoMark\InertiaWordpress\Request;

use Closure;
use Carbon\CarbonInterval;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use EvoMark\InertiaWordpress\Inertia;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Support\Arrayable;
use EvoMark\InertiaWordpress\Helpers\Header;
use EvoMark\InertiaWordpress\Props\LazyProp;
use EvoMark\InertiaWordpress\Props\DeferProp;
use EvoMark\InertiaWordpress\Props\MergeProp;
use EvoMark\InertiaWordpress\Helpers\Settings;
use EvoMark\InertiaWordpress\Props\AlwaysProp;
use EvoMark\InertiaWordpress\Props\OptionalProp;
use EvoMark\InertiaWordpress\Props\OnceProp;
use EvoMark\InertiaWordpress\Props\ScrollProp;
use EvoMark\InertiaWordpress\Contracts\Deferrable;
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Contracts\Onceable;
use EvoMark\InertiaWordpress\Contracts\ProvidesInertiaProperties;
use EvoMark\InertiaWordpress\Data\RenderContext;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Ssr\HttpGateway;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Responses\StandardResponse;
use EvoMark\InertiaWordpress\Data\SsrResponse as DataSsrResponse;
use EvoMark\InertiaWordpress\Helpers\HookActions;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Helpers\Wordpress;
use EvoMark\InertiaWordpress\Resources\UserResource;
use EvoMark\InertiaWordpress\Traits\ResolvesCallables;

class RequestHandler
{
    use ResolvesCallables;

    /** Flash key used to carry Inertia::clearHistory() across a redirect. */
    public const CLEAR_HISTORY_FLASH_KEY = 'clear_history';

    /** The server-rendered page for this request, once SSR has been attempted. */
    protected ?DataSsrResponse $ssrResponse = null;
    protected bool $ssrAttempted = false;

    protected bool $clearHistory = false;
    protected bool $encryptHistory = false;

    /** Flash key holding data from Inertia::flash() until the next page render. */
    public const FLASH_KEY = 'flash';

    /**
     * Flash data delivered with this request's page, or null until it has been read.
     * Read once so page.flash and the (deprecated) props.flash always agree.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $flashed = null;

    /** Shared + page props with providers expanded, cached while building one page. */
    protected ?Collection $allProps = null;

    protected $version = null;
    protected $url = null;
    protected $component = null;
    protected Collection $props;
    protected Collection $sharedProps;
    protected Collection $headers;
    protected $cacheFor = [];

    protected bool $isInertia = false;

    /** @var callable|null Decides the page object's url; see setUrlResolver() */
    protected $urlResolver = null;

    public function __construct()
    {
        $this->url = $this->setUrl();
        $this->encryptHistory = Settings::get('history_encrypt');
        $this->props = collect();
        $this->sharedProps = collect();
        $this->headers = collect(getallheaders())->mapWithKeys(function (mixed $value, string $key) {
            return [strtolower($key) => $value];
        });
        $this->isInertia = $this->checkForInertiaRequest();
    }

    /**
     * Called by the theme's root template via inertia_head(). Prints the server-rendered
     * head elements when SSR succeeds, then wp_head().
     */
    public function head()
    {
        $ssr = $this->getSsrResponse();

        if ($ssr === null) {
            return StandardResponse::handleHead();
        }

        foreach ($ssr->head as $element) {
            echo $element . PHP_EOL;
        }

        wp_head();
    }

    /**
     * Called by the theme's root template via inertia_body(). Prints the server-rendered
     * page when SSR succeeds, otherwise the root element for client-side rendering.
     */
    public function body($args = [])
    {
        $ssr = $this->getSsrResponse();

        if ($ssr === null) {
            return StandardResponse::handleBody($args);
        }

        wp_body_open();

        echo $ssr->body;

        wp_footer();
    }

    /**
     * Server-render the page, at most once per request, whichever of head() or body() runs
     * first. Null when SSR is disabled or fails, meaning client-side rendering.
     */
    public function getSsrResponse(): ?DataSsrResponse
    {
        if (! $this->ssrAttempted) {
            $this->ssrAttempted = true;

            if (Settings::get('ssr_enabled')) {
                $this->ssrResponse = Container::getInstance()->get(HttpGateway::class)->dispatch($this->getPageData());
            }
        }

        return $this->ssrResponse;
    }

    /**
     * The current asset version. Always a string so it compares cleanly with the
     * X-Inertia-Version request header; an unset version is "".
     */
    public function getVersion(): string
    {
        // A closure version is resolved on first use and then cached.
        if ($this->useAsCallable($this->version)) {
            $this->version = $this->resolveCallable($this->version);
        }

        return (string) ($this->version ?? '');
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * A GET Inertia visit whose X-Inertia-Version doesn't match the current asset version
     * must be answered with a 409 so the client does a full page reload.
     */
    public function hasVersionMismatch(): bool
    {
        return $this->isInertia()
            && RequestResponse::getMethod() === 'GET'
            && (string) $this->getHeader(Header::VERSION, '') !== $this->getVersion();
    }

    public function setUrl()
    {
        return $_SERVER['REQUEST_URI']
            ?? '/';
    }

    /**
     * Use a callback to decide the page object's `url`. It's given the default URL (the
     * request URI) and this request handler, and returns the URL to use. Null restores the default.
     */
    public function setUrlResolver(?callable $resolver): void
    {
        $this->urlResolver = $resolver;
    }

    /**
     * The page object's `url`: the request URI, unless a resolver has been set.
     */
    public function getUrl(): string
    {
        $url = (string) $this->url;

        if ($this->urlResolver === null) {
            return $url;
        }

        return (string) call_user_func($this->urlResolver, $url, $this);
    }

    public function setComponent(string $component)
    {
        $this->component = $component;
    }

    /**
     * @param  array<array-key, mixed>|Arrayable|ProvidesInertiaProperties  $props
     */
    public function setProps(array|Arrayable|ProvidesInertiaProperties $props)
    {
        $this->props = match (true) {
            // A provider passed on its own is treated as a single keyless entry.
            $props instanceof ProvidesInertiaProperties => collect([$props]),
            $props instanceof Arrayable => collect($props->toArray()),
            default => collect($props),
        };
    }

    public function setClearHistory(): void
    {
        $this->clearHistory = true;
    }

    public function setEncryptHistory(bool $encrypt = true): void
    {
        $this->encryptHistory = $encrypt;
    }

    /**
     * The protocol identifies Inertia visits by the X-Inertia header alone.
     */
    public function checkForInertiaRequest(): bool
    {
        return $this->getHeader(Header::INERTIA) === "true";
    }

    public function isPartial(): bool
    {
        $header = $this->getHeader(Header::PARTIAL_COMPONENT);

        if (! is_string($header) || $header === '') {
            return false;
        }

        // The client echoes back the component it received, which carries a "?template=..."
        // suffix when a page template is selected (see Wordpress::getTemplate()).
        $component = explode('?', $header, 2)[0];

        return $component === $this->component;
    }

    public function isInertia(): bool
    {
        return $this->isInertia;
    }

    public function getPageData(): array
    {
        // Expand ProvidesInertiaProperties afresh for each page build (see allProps()).
        $this->allProps = null;

        // Picks up (and consumes) a clearHistory() made before a redirect. Kept on the
        // handler so repeated getPageData() calls in one request (e.g. SSR) agree.
        if (RequestResponse::getFlashData(self::CLEAR_HISTORY_FLASH_KEY, false)) {
            $this->clearHistory = true;
        }

        // Key order follows the protocol's page object.
        return array_merge(
            [
                'component' => $this->component . Wordpress::getTemplate(),
                'props' => $this->resolveProperties(),
                'url' => $this->getUrl(),
                'version' => $this->getVersion(),
                'clearHistory' => $this->clearHistory,
                'encryptHistory' => $this->encryptHistory,
            ],
            $this->resolveMergeProps(),
            $this->resolveDeferredProps(),
            $this->resolveCacheDirections(),
            $this->resolveScrollProps(),
            $this->resolveOnceProps(),
            $this->resolveFlashData()
        );
    }

    /**
     * scrollProps metadata for the client's <InfiniteScroll>: pagination details per scroll
     * prop sent in this response, and whether the client asked to reset (replace) it.
     */
    private function resolveScrollProps(): array
    {
        $resetProps = $this->getHeaderList(Header::RESET);
        $isPartial = $this->isPartial();

        $scrollProps = $this->getMergePropsForRequest(false)
            ->filter(fn ($prop) => $prop instanceof ScrollProp)
            // A deferred scroll prop isn't sent on the first visit, so has no metadata yet.
            ->reject(fn (ScrollProp $prop) => ! $isPartial && $prop->shouldDefer())
            ->mapWithKeys(fn (ScrollProp $prop, string $key) => [$key => [
                ...$prop->metadata(),
                'reset' => in_array($key, $resetProps, true),
            ]]);

        return $scrollProps->isNotEmpty() ? ['scrollProps' => $scrollProps->toArray()] : [];
    }

    /**
     * Once props the client reports it already has cached (by cache key).
     *
     * @return array<int, string>
     */
    private function getExceptOnceProps(): array
    {
        return $this->getHeaderList(Header::EXCEPT_ONCE_PROPS);
    }

    /**
     * Whether a prop is a once prop the client already has, so it can be skipped.
     */
    private function isLoadedOnceProp(mixed $prop, string $key, array $exceptOnceProps): bool
    {
        return $prop instanceof Onceable
            && $prop->shouldResolveOnce()
            && ! $prop->shouldBeRefreshed()
            && in_array($prop->getKey() ?? $key, $exceptOnceProps, true);
    }

    /**
     * Skip once props the client already has. Only on full Inertia visits: a partial reload
     * asks for its props explicitly, and a first (non-Inertia) visit has no client cache.
     */
    private function resolveOnceProperties(Collection $props): Collection
    {
        if (! $this->isInertia() || $this->isPartial()) {
            return $props;
        }

        $exceptOnceProps = $this->getExceptOnceProps();

        if (count($exceptOnceProps) === 0) {
            return $props;
        }

        return $props->reject(fn ($prop, $key) => $this->isLoadedOnceProp($prop, (string) $key, $exceptOnceProps));
    }

    /**
     * onceProps metadata: cache key => the prop it holds and when the cached copy expires.
     * Listed even when skipped, so the client knows to keep using its cached copy.
     */
    private function resolveOnceProps(): array
    {
        $onceProps = $this->allProps()
            ->filter(fn ($prop) => $prop instanceof Onceable && $prop->shouldResolveOnce());

        if ($this->isPartial()) {
            $only = $this->getHeaderList(Header::PARTIAL_ONLY);

            if (count($only)) {
                $onceProps = $onceProps->only($only);
            }

            $onceProps = $onceProps->except($this->getHeaderList(Header::PARTIAL_EXCEPT));
        }

        $onceProps = $onceProps->mapWithKeys(fn (Onceable $prop, string $key) => [
            $prop->getKey() ?? $key => [
                'prop' => $key,
                'expiresAt' => $prop->expiresAt(),
            ],
        ]);

        return $onceProps->isNotEmpty() ? ['onceProps' => $onceProps->toArray()] : [];
    }

    /**
     * Add flash data. Before this request's page has read its flash data, it's stored for the
     * next page render (surviving a redirect); afterwards it joins the current page's data.
     *
     * @param  array<string, mixed>  $data
     */
    public function flash(array $data): void
    {
        if ($this->flashed !== null) {
            $this->flashed = array_merge($this->flashed, $data);
            return;
        }

        $stored = (array) RequestResponse::getFlashData(self::FLASH_KEY, []);
        RequestResponse::setFlashData(self::FLASH_KEY, array_merge($stored, $data));
    }

    /**
     * The flash data for this request's page, read (and consumed) on first use.
     *
     * @return array<string, mixed>
     */
    public function getFlashed(): array
    {
        if ($this->flashed === null) {
            $this->flashed = (array) RequestResponse::getFlashData(self::FLASH_KEY, []);
        }

        return $this->flashed;
    }

    /**
     * Flash data is a top-level page key (not a prop), so the client can fire its flash
     * event and keep it out of history. Omitted when there is nothing flashed.
     */
    private function resolveFlashData(): array
    {
        $flashed = $this->getFlashed();

        return count($flashed) ? ['flash' => $flashed] : [];
    }

    /**
     * Shared props overlaid with the page's own props: page props win on key conflicts,
     * and shared keys come first so their dot props are unpacked before the page's.
     */
    private function allProps(): Collection
    {
        if ($this->allProps === null) {
            $this->allProps = $this->expandPropsProviders($this->sharedProps)
                ->merge($this->expandPropsProviders($this->props));

            // Apply each scroll prop's append/prepend direction before any merge metadata is built.
            $this->allProps
                ->filter(fn ($prop) => $prop instanceof ScrollProp)
                ->each(fn (ScrollProp $prop) => $prop->configureMergeIntent($this));
        }

        return $this->allProps;
    }

    /**
     * Replace keyless ProvidesInertiaProperties entries with the props they provide, in
     * place, so later keys still override earlier ones.
     */
    private function expandPropsProviders(Collection $props): Collection
    {
        $context = null;
        $expanded = [];

        foreach ($props as $key => $value) {
            if (is_int($key) && $value instanceof ProvidesInertiaProperties) {
                $context ??= new RenderContext((string) $this->component, $this);
                $expanded = array_merge($expanded, collect($value->toInertiaProperties($context))->all());
            } else {
                $expanded[$key] = $value;
            }
        }

        return collect($expanded);
    }

    private function resolveProperties(): array
    {
        $props = $this->allProps();
        $props = $this->resolvePartialProperties($props);
        $props = $this->resolveOnceProperties($props);
        $props = $this->resolveArrayableProperties($props);
        $props = $this->resolveAlways($props);
        $props = $this->resolvePropertyInstances($props);
        return $props->toArray();
    }

    private function resolvePartialProperties(Collection $props): Collection
    {
        if (! $this->isPartial()) {
            return $props->filter(function ($prop) {
                return ! ($prop instanceof IgnoreFirstLoad)
                    && ! ($prop instanceof Deferrable && $prop->shouldDefer());
            });
        }

        $only = $this->getHeaderList(Header::PARTIAL_ONLY);
        $except = $this->getHeaderList(Header::PARTIAL_EXCEPT);

        $all = $props->all();

        if (count($only)) {
            $newProps = [];

            foreach ($only as $key) {
                Arr::set($newProps, $key, Arr::get($all, $key));
            }

            $all = $newProps;
        }

        if (count($except)) {
            Arr::forget($all, $except);
        }

        return collect($all);
    }

    private function resolveArrayableProperties(Collection $props, bool $unpackDotProps = true): Collection
    {
        foreach ($props as $key => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if (is_array($value)) {
                // Keep nested values as plain arrays so Arr::set() can unpack dot props into them.
                $value = $this->resolveArrayableProperties(collect($value), false)->all();
            }

            // Only closures and invokable objects; strings and arrays are data, never callables.
            $value = $this->resolveCallable($value);

            if ($unpackDotProps && str_contains($key, '.')) {
                // Arr::set() returns the innermost array, not the root, so mutate a copy in place.
                $all = $props->all();
                unset($all[$key]);
                Arr::set($all, $key, $value);
                $props = collect($all);
            } else {
                $props->put($key, $value);
            }
        }

        return $props;
    }

    /**
     * Resolve `always` properties that should always be included on all visits, regardless of "only" or "except" requests.
     */
    private function resolveAlways(Collection $props): Collection
    {
        $allProps = $this->allProps();
        $always = $allProps->filter(function ($prop) {
            return $prop instanceof AlwaysProp;
        });

        return $props->merge($always);
    }

    private function resolvePropertyInstances(Collection $props): Collection
    {
        foreach ($props as $key => $value) {
            $resolveUserFunction = collect([
                Closure::class,
                LazyProp::class,
                OptionalProp::class,
                DeferProp::class,
                AlwaysProp::class,
                MergeProp::class,
                OnceProp::class,
                ScrollProp::class,
            ])->first(fn ($class) => $value instanceof $class);

            if ($resolveUserFunction) {
                $value = call_user_func($value);
            }

            if ($value instanceof PromiseInterface) {
                $value = $value->wait();
            }

            if (is_array($value)) {
                $value = $this->resolvePropertyInstances(collect($value));
            }

            $props->put($key, $value);
        }

        return $props;
    }

    /**
     * Merge props this response actually sends: on a partial reload, only those requested.
     *
     * @param  bool  $rejectResetProps  Leave out props the client asked to reset (not merge)
     * @return Collection<string, Mergeable>
     */
    private function getMergePropsForRequest(bool $rejectResetProps = true): Collection
    {
        $mergeProps = $this->allProps()
            ->filter(fn ($prop) => $prop instanceof Mergeable && $prop->shouldMerge());

        if ($rejectResetProps) {
            $mergeProps = $mergeProps->except($this->getHeaderList(Header::RESET));
        }

        if ($this->isPartial()) {
            $only = $this->getHeaderList(Header::PARTIAL_ONLY);

            if (count($only)) {
                $mergeProps = $mergeProps->only($only);
            }

            $mergeProps = $mergeProps->except($this->getHeaderList(Header::PARTIAL_EXCEPT));
        }

        return $mergeProps;
    }

    private function resolveMergeProps(): array
    {
        $mergeProps = $this->getMergePropsForRequest();

        // Empty lists are left out, so pages without merge props keep the plain page object.
        return array_filter([
            'mergeProps' => $this->resolveAppendMergeProps($mergeProps),
            'prependProps' => $this->resolvePrependMergeProps($mergeProps),
            'deepMergeProps' => $this->resolveDeepMergeProps($mergeProps),
            'matchPropsOn' => $this->resolveMergeMatchingKeys($mergeProps),
        ], fn (array $keys) => count($keys) > 0);
    }

    /**
     * Props appended to: whole props ("posts") and nested paths ("posts.data").
     *
     * @param  Collection<string, Mergeable>  $mergeProps
     * @return array<int, string>
     */
    private function resolveAppendMergeProps(Collection $mergeProps): array
    {
        [$rootProps, $nestedProps] = $mergeProps
            ->reject(fn (Mergeable $prop) => $prop->shouldDeepMerge())
            ->partition(fn (Mergeable $prop) => $prop->appendsAtRoot());

        return $nestedProps
            ->flatMap(fn (Mergeable $prop, string $key) => collect($prop->appendsAtPaths())->map(fn ($path) => $key . '.' . $path))
            ->merge($rootProps->keys())
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Props prepended to: whole props and nested paths.
     *
     * @param  Collection<string, Mergeable>  $mergeProps
     * @return array<int, string>
     */
    private function resolvePrependMergeProps(Collection $mergeProps): array
    {
        [$rootProps, $nestedProps] = $mergeProps
            ->reject(fn (Mergeable $prop) => $prop->shouldDeepMerge())
            ->partition(fn (Mergeable $prop) => $prop->prependsAtRoot());

        return $nestedProps
            ->flatMap(fn (Mergeable $prop, string $key) => collect($prop->prependsAtPaths())->map(fn ($path) => $key . '.' . $path))
            ->merge($rootProps->keys())
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * @param  Collection<string, Mergeable>  $mergeProps
     * @return array<int, string>
     */
    private function resolveDeepMergeProps(Collection $mergeProps): array
    {
        return $mergeProps
            ->filter(fn (Mergeable $prop) => $prop->shouldDeepMerge())
            ->keys()
            ->toArray();
    }

    /**
     * Keys the client matches items on when merging, prefixed with the prop name ("posts.data.id").
     *
     * @param  Collection<string, Mergeable>  $mergeProps
     * @return array<int, string>
     */
    private function resolveMergeMatchingKeys(Collection $mergeProps): array
    {
        return $mergeProps
            ->map(fn (Mergeable $prop, string $key) => collect($prop->matchesOn())->map(fn ($strategy) => $key . '.' . $strategy)->toArray())
            ->flatten()
            ->values()
            ->toArray();
    }

    private function resolveDeferredProps(): array
    {
        if ($this->isPartial()) {
            return [];
        }

        $allProps = $this->allProps();
        $exceptOnceProps = $this->getExceptOnceProps();

        $deferredProps = collect($allProps)
            ->filter(function ($prop) {
                return $prop instanceof Deferrable && $prop->shouldDefer();
            })
            // A deferred once prop the client already has doesn't need fetching again.
            ->reject(fn ($prop, $key) => $this->isLoadedOnceProp($prop, (string) $key, $exceptOnceProps))
            ->map(function ($prop, $key) {
                /** @var Deferrable $prop */
                return [
                    'key' => $key,
                    'group' => $prop->group(),
                ];
            })
            ->groupBy('group')
            ->map
            ->pluck('key');

        return $deferredProps->isNotEmpty() ? ['deferredProps' => $deferredProps->toArray()] : [];
    }

    private function resolveCacheDirections(): array
    {
        if (count($this->cacheFor) === 0) {
            return [];
        }

        return [
            'cache' => collect($this->cacheFor)->map(function ($value) {
                if ($value instanceof CarbonInterval) {
                    return $value->totalSeconds;
                }

                return intval($value);
            }),
        ];
    }

    /**
     * Parse a comma-separated header (e.g. X-Inertia-Partial-Data) into a list of keys.
     *
     * @return array<int, string>
     */
    public function getHeaderList(string $header): array
    {
        $value = $this->getHeader($header, '');

        if (! is_string($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($key) => $key !== ''));
    }

    public function getHeader(string $header, mixed $defaultValue = null): mixed
    {
        if ($this->headers->has($header) === false) {
            return $defaultValue;
        }

        $value = $this->headers[$header];
        return $value ?? $defaultValue;
    }

    public function hasHeader(string $header): bool
    {
        return $this->headers->has($header);
    }

    public function flushShared()
    {
        $this->sharedProps = collect();
    }

    /**
     * @param  mixed  $default
     * @return mixed
     */
    public function getShared(?string $key = null, $default = null)
    {
        if ($key) {
            return $this->sharedProps->get($key, $default);
        }

        return $this->sharedProps->toArray();
    }

    /**
     * @param  string|array|Arrayable|ProvidesInertiaProperties  $key
     * @param  mixed  $value
     */
    public function share($key, $value = null): void
    {
        // Collection::merge() returns a new collection rather than mutating, so reassign it.
        if ($key instanceof ProvidesInertiaProperties) {
            // Kept as a keyless entry; expanded when the page is built (see allProps()).
            $this->sharedProps->push($key);
        } elseif (is_array($key)) {
            $this->sharedProps = $this->sharedProps->merge($key);
        } elseif ($key instanceof Arrayable) {
            $this->sharedProps = $this->sharedProps->merge($key->toArray());
        } else {
            $this->sharedProps->put($key, $value);
        }
    }

    public function setGlobalShares()
    {
        $this->share('errors', Inertia::always(RequestResponse::getFormattedErrors()));
        // Deprecated: flash data now lives on the top-level page.flash key. Kept as a prop for
        // backwards compatibility; to be removed in a future release.
        $this->share('flash', Inertia::always(fn () => $this->getFlashed() ?: (object) []));
        $this->share('wp', [
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'charset' => get_bloginfo('charset'),
            'isRtl' => is_rtl(),
            'language' => get_bloginfo('language'),
            'isAdminBarShowing' => is_admin_bar_showing(),
            'adminBar' => $this->isInertia() ? fn () => Wordpress::getAdminBar() : null,
            'homeUrl' => home_url(),
            'restUrl' => get_rest_url(),
            'user' => is_user_logged_in() ? UserResource::single(wp_get_current_user()) : null,
            'userCapabilities' => is_user_logged_in() ? Wordpress::getUserCapabilities(wp_get_current_user()) : null,
            'userRoles' => is_user_logged_in() ? wp_get_current_user()->roles : null,
            'canRegister' => boolval(get_option('users_can_register')),
            'logo' => Wordpress::getCustomLogo(),
            'menus' => Wordpress::getNavigationMenus(),
            'nonces' => [
                'rest' => wp_create_nonce('wp_rest'),
                'ajax' => wp_create_nonce('ajax_nonce'),
                'logout' => wp_create_nonce('inertia_logout'),
            ],
            'comments' => [
                'showAvatars' => boolval(get_option('show_avatars')),
                'perPage' => intval(get_option('comments_per_page')),
                'requireAuth' => boolval(get_option('comment_registration')),
                'requireInfo' => boolval(get_option('require_name_email')),
                'closeForOld' => boolval(get_option('close_comments_for_old_posts')),
                'daysForOld' => intval(get_option('close_comments_days_old')),
            ],
            'postsPerPage' => intval(get_option('posts_per_page')),
            'dateFormat' => get_option('date_format'),
            'timeFormat' => get_option('time_format'),
            'startOfWeek' => intval(get_option('start_of_week')),
        ]);

        /**
         * Call to add any needed global shares before the response factory is called
         *
         * @since 0.4.0
         */
        do_action(HookActions::SET_GLOBAL_SHARES);
    }
}
