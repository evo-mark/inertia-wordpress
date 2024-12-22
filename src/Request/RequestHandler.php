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
use EvoMark\InertiaWordpress\Contracts\Mergeable;
use EvoMark\InertiaWordpress\Responses\SsrResponse;
use EvoMark\InertiaWordpress\Contracts\IgnoreFirstLoad;
use EvoMark\InertiaWordpress\Responses\StandardResponse;
use EvoMark\InertiaWordpress\Data\SsrResponse as DataSsrResponse;
use EvoMark\InertiaWordpress\Helpers\HookActions;
use EvoMark\InertiaWordpress\Helpers\RequestResponse;
use EvoMark\InertiaWordpress\Helpers\Wordpress;
use EvoMark\InertiaWordpress\Resources\UserResource;

class RequestHandler
{
    protected ?DataSsrResponse $ssr;
    public bool $ssrEnabled = false;
    protected string $ssrUrl = "";

    protected bool $clearHistory = false;
    protected bool $encryptHistory = false;

    protected $version = null;
    protected $url = null;
    protected $component = null;
    protected Collection $props;
    protected Collection $sharedProps;
    protected Collection $headers;
    protected $cacheFor = [];

    protected bool $isInertia = false;

    public function __construct()
    {
        $this->url = $this->setUrl();
        $settings = Settings::get(["ssr_enabled", "ssr_url", "history_encrypt", "root_template", "entry_file", "entry_namespace"]);
        $this->ssrEnabled = $settings['ssr_enabled'];
        $this->ssrUrl = $settings['ssr_url'];
        $this->encryptHistory = $settings['history_encrypt'];
        $this->props = collect();
        $this->sharedProps = collect();
        $this->headers = collect(getallheaders())->mapWithKeys(function (mixed $value, string $key) {
            return [strtolower($key) => $value];
        });
        $this->isInertia = $this->checkForInertiaRequest();
    }

    /**
     * Called by the theme's app.php file, standard/ssr only
     */
    public function head()
    {
        if ($this->ssrEnabled === true) {
            return SsrResponse::handleHead();
        } else {
            return StandardResponse::handleHead();
        }
    }

    /**
     * Called by the theme's app.php file, standard/ssr only
     */
    public function body($args)
    {
        if ($this->ssrEnabled === true) {
            return SsrResponse::handleBody($args);
        } else {
            return StandardResponse::handleBody($args);
        }
    }

    public function getSsr(): DataSsrResponse
    {
        return $this->ssr;
    }

    public function setSsr(?DataSsrResponse $ssr): void
    {
        $this->ssr = $ssr;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function setUrl()
    {
        return $_SERVER['REQUEST_URI']
            ?? '/';
    }

    public function setComponent(string $component)
    {
        $this->component = $component;
    }

    public function setProps(array $props)
    {
        $this->props = collect($props);
    }

    public function setClearHistory(): void
    {
        $this->clearHistory = true;
    }

    public function setEncryptHistory(bool $encrypt = true): void
    {
        $this->encryptHistory = $encrypt;
    }

    public function checkForInertiaRequest(): bool
    {
        return $this->getHeader('x-requested-with') === "XMLHttpRequest" &&
            $this->getHeader(Header::INERTIA) === "true";
    }

    public function isPartial(): bool
    {
        return $this->getHeader(Header::PARTIAL_COMPONENT) === $this->component;
    }

    public function isInertia(): bool
    {
        return $this->isInertia;
    }

    public function getPageData(): array
    {
        return array_merge(
            [
                'url' => $this->url,
                'props' => $this->resolveProperties(),
                'version' => $this->version,
                'component' => $this->component,
                'clearHistory' => $this->clearHistory,
                'encryptHistory' => $this->encryptHistory,
            ],
            $this->resolveMergeProps(),
            $this->resolveDeferredProps(),
            $this->resolveCacheDirections()
        );
    }

    private function resolveProperties(): array
    {
        $props = $this->props->merge($this->sharedProps);
        $props = $this->resolvePartialProperties($props);
        $props = $this->resolveArrayableProperties($props);
        $props = $this->resolveAlways($props);
        $props = $this->resolvePropertyInstances($props);
        return $props->toArray();
    }

    private function resolvePartialProperties(Collection $props): Collection
    {
        if (! $this->isPartial()) {
            return $props->filter(function ($prop) {
                return ! ($prop instanceof IgnoreFirstLoad);
            });
        }

        $only = $this->headers->has(Header::PARTIAL_ONLY) ? array_filter(explode(",", $this->headers[Header::PARTIAL_ONLY])) : [];
        $except = $this->headers->has(Header::PARTIAL_EXCEPT) ? array_filter(explode(",", $this->headers[Header::PARTIAL_EXCEPT])) : [];

        if (count($only)) {
            $newProps = [];

            foreach ($only as $key) {
                Arr::set($newProps, $key, $props->get($key));
            }

            $props = collect($newProps);
        }

        if (count($except)) {
            $props->forget($except);
        }

        return $props;
    }

    private function resolveArrayableProperties(Collection $props, bool $unpackDotProps = true): Collection
    {
        foreach ($props as $key => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if (is_array($value)) {
                $value = $this->resolveArrayableProperties(collect($value), false);
            }

            if ($value instanceof Closure || is_callable($value)) {
                $value = call_user_func($value);
            }

            if ($unpackDotProps && str_contains($key, '.')) {
                $props = collect(Arr::set($props->all(), $key, $value));
                $props->forget($key);
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
        $allProps = $this->props->merge($this->sharedProps);
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
            ])->first(fn($class) => $value instanceof $class);

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

    private function resolveMergeProps(): array
    {
        $allProps = $this->props->merge($this->sharedProps);
        $resetProps = collect(explode(',', $this->getHeader(Header::RESET, "")));

        $mergeProps = collect($allProps)
            ->filter(function ($prop) {
                return $prop instanceof Mergeable;
            })
            ->filter(function ($prop) {
                /** @var Mergeable $prop */
                return $prop->shouldMerge();
            })
            ->filter(function ($prop, $key) use ($resetProps) {
                return ! $resetProps->contains($key);
            })
            ->keys();

        return $mergeProps->isNotEmpty() ? ['mergeProps' => $mergeProps->toArray()] : [];
    }

    private function resolveDeferredProps(): array
    {
        if ($this->isPartial()) {
            return [];
        }

        $allProps = $this->props->merge($this->sharedProps);

        $deferredProps = collect($allProps)
            ->filter(function ($prop) {
                return $prop instanceof DeferProp;
            })
            ->map(function ($prop, $key) {
                /** @var DeferProp $prop */
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
     * @param  string|array|Arrayable  $key
     * @param  mixed  $value
     */
    public function share($key, $value = null): void
    {
        if (is_array($key)) {
            $this->sharedProps->merge($key);
        } elseif ($key instanceof Arrayable) {
            $this->sharedProps->merge($key->toArray());
        } else {
            $this->sharedProps->put($key, $value);
        }
    }

    public function setGlobalShares()
    {
        $this->share('errors', Inertia::always(RequestResponse::getFormattedErrors()));
        $this->share('flash', Inertia::always(RequestResponse::getFlashData('flash', (object)[])));
        $this->share('wp', [
            'name' => get_bloginfo('name'),
            'adminBar' => $this->isInertia() ? fn() => Wordpress::getAdminBar() : null,
            'restUrl' => get_rest_url(),
            'user' => is_user_logged_in() ? UserResource::single(wp_get_current_user()) : null,
            'userCapabilities' => is_user_logged_in() ? Wordpress::getUserCapabilities(wp_get_current_user()) : null,
            'logo' => Wordpress::getCustomLogo(),
            'homeUrl' => home_url(),
            'menus' => Wordpress::getNavigationMenus(),
        ]);

        /**
         * Call to add any needed global shares before the response factory is called
         *
         * @since 0.4.0
         */
        do_action(HookActions::SET_GLOBAL_SHARES);
    }
}
