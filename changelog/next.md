- **Feature**: New `Inertia::version($version)` method for setting the asset version; accepts a string, number or closure (resolved lazily)

- **Improvement**: All responses (Inertia JSON, 409 location and redirects) are now sent through a single `ResponseEmitter`, making them testable
- **Improvement**: `Inertia::redirect()` now defaults to a 302 status, so the status argument is optional

- **BugFix**: Redirects after a `PUT`, `PATCH` or `DELETE` Inertia request are now sent as `303` (previously always `302`, which could make the browser repeat the request), including the WooCommerce address update endpoint
- **BugFix**: Flash data and validation errors are no longer lost when an asset version mismatch triggers a 409 reload
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

- **Security**: Prop values are only called if they are closures or invokable objects. Previously, any string or array that matched a function name was executed, so content like a site title or menu description could trigger PHP functions

- **Chore**: Added a PHPUnit unit test suite (Brain Monkey, no database required), run with `composer test`

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
