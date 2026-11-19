- **Feature**: New `Inertia::version($version)` method for setting the asset version; accepts a string, number or closure (resolved lazily)
- **Feature**: New `Inertia::getShared($key = null, $default = null)` method for reading shared props
- **Feature**: New `Inertia::resolveUrlUsing($resolver)` method for customising the page's `url`, e.g. to strip tracking parameters
- **Feature**: New **Use Script Element** setting (Inertia → Settings → Initial Page) embeds the initial page data in a `<script type="application/json">` element instead of the `data-page` attribute, making the HTML smaller. Off by default
- **Feature**: New `withSsrRender()` helper in the Vue package for SSR entries (see below)
- **Feature**: Merge props now support Inertia v2's full merging options: `prepend()`, nested paths via `append('data')`/`prepend('data')`, matching items with `matchOn('id')`, and deep merging with `deepMerge()` (also on deferred props) and the new `Inertia::deepMerge($value)`. The page object now includes `prependProps`, `deepMergeProps` and `matchPropsOn` when used
- **Feature**: Flash data is now sent on the top-level `page.flash` key, as in Inertia v2, so the client fires its `flash` event / `onFlash` callback and keeps flash data out of browser history. `Inertia::flash()` now also accepts an array of key => value pairs and enum keys
- **Feature**: Once props: `Inertia::once($callback)` and `Inertia::shareOnce($key, $callback)` send a prop on the first visit, after which the client caches it and the server skips resolving it. Supports expiry (`->until()`), custom cache keys (`->as()`) and forced refreshes (`->fresh()`), and deferred, optional and merge props can use `->once()` too
- **Feature**: Props providers: classes implementing `ProvidesInertiaProperties` contribute several props at once when added to props or shared props without a key, receiving a `RenderContext` with the component and request. `render()`, `setProps()` and `share()` now also accept a provider or `Arrayable` as the whole props argument
- **Feature**: Infinite scroll props: `Inertia::scroll($value, $wrapper, $metadata)` for Inertia's `<InfiniteScroll>` component. Pagination details come from WordPress's main query by default, from a custom `WP_Query` via `ScrollMetadata::fromQuery()`, or from any `ProvidesScrollMetadata`. Scroll props can be deferred, and new items are appended or prepended depending on the scroll direction
- **Feature**: New `wp inertia:check-ssr` command reports whether the SSR server is running, exiting non-zero if not
- **Feature**: New `inertia_wordpress_ssr_failed` action fires when server-side rendering falls back to client-side rendering; failures are also logged when `WP_DEBUG` is on
- **Feature**: New `inertia_wordpress_ssr_client_options` filter for the Guzzle options used to call the SSR server

- **Improvement**: All responses (Inertia JSON, 409 location and redirects) are now sent through a single `ResponseEmitter`, making them testable
- **Improvement**: `Inertia::redirect()` now defaults to a 302 status, so the status argument is optional
- **Improvement**: SSR rendering moved into a new `Ssr\HttpGateway`. It's skipped when the theme's SSR bundle hasn't been built, and requests to the SSR server are made at most once per page
- **Improvement**: Upgraded admin panel to Vuetify 4 and Tailwind 4.
- **Improvement**: Inertia requests for `?paged=N` URLs (as made by `<InfiniteScroll>`) are no longer canonical-redirected to `/page/N/`, saving a round trip for every page loaded. Full page loads are still redirected as normal

- **BugFix**: Requests to the SSR server now time out (1s to connect, 5s in total), so a hung SSR server no longer stalls every page load
- **BugFix**: Fatal error when SSR was enabled and a root template called `inertia_body()` without `inertia_head()`
- **BugFix**: `wp_body_open()` is now called on server-rendered pages, so plugins hooking it work with SSR enabled
- **BugFix**: Redirects after a `PUT`, `PATCH` or `DELETE` Inertia request are now sent as `303` (previously always `302`, which could make the browser repeat the request), including the WooCommerce address update endpoint
- **BugFix**: Flash data and validation errors are no longer lost when an asset version mismatch triggers a 409 reload
- **BugFix**: The reload URL sent on an asset version mismatch no longer repeats the subdirectory on subdirectory installs and multisite subdirectory sites (e.g. `/blog/blog/about`)
- **BugFix**: `Inertia::clearHistory()` now survives a redirect, so clearing history before redirecting (e.g. after logout) works
- **BugFix**: First-time visitors no longer lose flash data when it is set more than once in a request (a new temporary ID was generated on every flash read/write)
- **BugFix**: Partial reloads now work on pages without a page template (previously `only`/`except` were ignored, so lazy and optional props could never load)
- **BugFix**: Partial reloads now accept nested dot paths such as `auth.user` in `only` and `except`
- **BugFix**: Page props now take precedence over shared props, so a page can override shared props like `errors`, and shared dot props (e.g. `auth.user`) combine with page dot props
- **BugFix**: Top-level dot props (e.g. `auth.user.can`) no longer wipe out the rest of their parent prop when unpacked
- **BugFix**: `Inertia::share()` now works when passed an array or `Arrayable`
- **BugFix**: `mergeProps` metadata on partial reloads now only lists props that were actually sent (respecting `only`, `except` and reset)
- **BugFix**: Inertia requests are now identified by the `X-Inertia` header alone, as the protocol specifies; `X-Requested-With` is no longer required
- **BugFix**: The asset version is now always a string, defaulting to `""`, so `Inertia::getVersion()` no longer throws when no version is set and numeric versions match the request header
- **BugFix**: The root template is loaded with `require` instead of `require_once`, so it renders on every call (e.g. repeated renders in WP-CLI)
- **BugFix**: `new DeferProp()` now defaults its group to `default`
- **BugFix**: Inertia responses now send `Vary: X-Inertia` instead of `Vary: Accept`
- **BugFix**: The root element no longer renders an empty `class=""` attribute, and its `id`/`class` are now escaped
- **BugFix**: Page object keys now follow the protocol order (`component`, `props`, `url`, `version`, ...)
- **BugFix**: Fatal error on boot caused by the update checker's versioned `Vcs\Api` class not being found after a library update
- **BugFix**: Vue SSR rendered `[object Promise]` instead of the page (causing a hydration mismatch) with Vue 3.5.44+, which no longer awaits the Promise `@inertiajs/vue3` 2.x passes as `innerHTML`. The Vue theme stub's `ssr.js` now uses the new `withSsrRender()` helper (see below)
- **BugFix**: The theme stub's PM2 `ecosystem.config.cjs` now sets `interpreter: "none"`, so PM2 runs `wp` directly instead of as a Node script (which crashed with `SyntaxError: Unexpected token`)

- **Security**: Prop values are only called if they are closures or invokable objects. Previously, any string or array that matched a function name was executed, so content like a site title or menu description could trigger PHP functions

- **Chore**: Added a PHPUnit unit test suite (Brain Monkey, no database required), run with `composer test`, replacing the old WordPress test library setup (`bin/install-wp-tests.sh`, CircleCI config and PHPUnit polyfills)
- **Chore**: Release builds now install Composer dependencies with `--no-dev`, so the plugin zip no longer ships test and tooling packages (`vendor` drops from ~43 MB to ~15 MB)
- **Chore**: Removed dead debug code from the Ninja Forms module

## Breaking Change

Props, shared props and prop helpers no longer call functions passed by name. If you pass a function name string or a `[Class, 'method']` array, wrap it in a closure:

```php
// Before
Inertia::share('settings', 'my_theme_settings');
Inertia::lazy([MyClass::class, 'load']);

// After
Inertia::share('settings', fn () => my_theme_settings());
Inertia::lazy(fn () => MyClass::load());
```

If any of your controllers override `InertiaController::render()`, update the `$props` parameter type to match the base class, which now accepts more than arrays. Otherwise PHP will refuse to load the controller with a "Declaration must be compatible" fatal error:

```php
use EvoMark\InertiaWordpress\Contracts\ProvidesInertiaProperties;
use Illuminate\Contracts\Support\Arrayable;

// Before
public function render(string $component, array $props = [])

// After
public function render(string $component, array|Arrayable|ProvidesInertiaProperties $props = [])
```

## Deprecation

`$page.props.flash` is deprecated and will be removed in a future release. Flash data is still sent there for now, alongside the new `$page.flash`. Update your theme to read flash data from the page object, or listen for the `flash` event:

```js
// Before
usePage().props.flash.success;

// After
usePage().flash.success;

router.on("flash", (event) => showToast(event.detail.flash.success));
```

## Updating Existing Themes

Theme stubs only apply to newly created themes. For an existing theme:

**Inertia client**: the new features in this release (once props, infinite scroll, page-level flash and the new merge options) need Inertia's client at `2.3.3` or later. Update your theme's `@inertiajs/vue3`, `@inertiajs/react` or `@inertiajs/svelte` dependency to `^2.3.3`, then reinstall and rebuild. New themes use this version by default.

**Vue SSR**: if server-rendered pages show `[object Promise]` or a hydration mismatch, wrap the options in `resources/js/ssr.js` with `withSsrRender()`:

```js
import { resolveInertiaPage, withSsrRender } from "@evo-mark/inertia-wordpress";

createServer((page) =>
	createInertiaApp(
		withSsrRender({
			page,
			render: renderToString,
			resolve: resolveInertiaPage(import.meta.glob("./pages/**/*.vue", { eager: true }), DefaultLayout),
			setup({ App, props, plugin }) {
				return createSSRApp({
					render: () => h(App, props),
				}).use(plugin);
			},
		}),
	),
);
```

Keep the `withSsrRender()` call inside the `createServer` callback, as it holds per-request state. Then reinstall your theme's packages so it picks up the updated `@evo-mark/inertia-wordpress` package (`pnpm install`), rebuild (`npm run build`) and restart SSR (`npm run ssr:restart`).
