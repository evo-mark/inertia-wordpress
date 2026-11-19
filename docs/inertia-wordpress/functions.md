# Functions

We've provided a wide array of helper functions to help you interact with the adapter from within your theme. All of the below are static methods on the same class.

```php
<?php

use EvoMark\InertiaWordpress\Inertia;
```

## Share

The central way to pass data from your controllers to your frontend.

Share can be called any time before a response is returned

| Param | Type   | Description                                               |
| ----- | ------ | --------------------------------------------------------- |
| key   | string | The key of the prop, will be placed on `$page.props[key]` |
| value | mixed  | Any serialisable value                                    |

```php
Inertia::share(string $key, mixed $value = null);
```

## Flush Shared

Deletes all previously shared props

```php
Inertia::flushShared();
```

## Encrypt History

:::warning
This feature is only available in secure (https) environments.
:::

If you want to enable encryption globally, for all requests, you can do so via the [Wordpress settings](/inertia-wordpress/settings) menu.

Alternatively, you can enable it per request by using the command below.

```php
Inertia::encryptHistory();
```

See the [Official InertiaJS website](https://inertiajs.com/history-encryption) for more details.

## Clear History

Prevents the browser history for any previous visits from being recalled. This is useful when logging a user out of your application.

```php
Inertia::clearHistory();
```

See the [Official InertiaJS website](https://inertiajs.com/history-encryption#clearing-history) for more details.

## Get Version

Retrieves a shasum of your frontend assets. You shouldn't need this, but it's included just in case.

```php
Inertia::getVersion(): string;
```

## Resolve URL Using

Changes the `url` sent on the page object, which the client puts in the address bar and browser history. By default it's the requested URI. The callback receives that default and the current request handler, and returns the URL to use:

```php
Inertia::resolveUrlUsing(?callable $resolver);

// e.g. keep tracking parameters out of the address bar and history
Inertia::resolveUrlUsing(function (string $url) {
    return remove_query_arg(['utm_source', 'utm_medium', 'utm_campaign'], $url);
});
```

Pass `null` to restore the default.

## Lazy

:::warning
Lazy props have been marked as deprecated in the Inertia-Laravel source code, so you should prefer using `optional` (see below) instead.
:::

Create a lazy prop for sharing

```php
Inertia::lazy(callable $callback);
```

## Optional

An optional prop is one that is **not** passed to the frontend by default. You must make a partial request (see link below) for it.

```php
Inertia::optional(callable $callback);
```

You might use it like so in your controller:

```php
return $this->render('some-folder/SomePage', [
    'someData' => Inertia::optional(function() {
        // Do some processing
        return $someData;
    }),
]);
```

or elsewhere

```php
Inertia::share('someData' => Inertia::optional(function() {
        // Do some processing
        return $someData;
    }),
);
```

See the [Official InertiaJS website](https://inertiajs.com/partial-reloads) for more details

## Defer

A deferred prop is one that is not sent in the initial page request, but is then loaded immediately afterwards.

By default, all deferred props get fetched in one request after the initial page is rendered, but you can choose to fetch data in parallel by grouping props together.

| Param    | Type     | Description                                    |
| -------- | -------- | ---------------------------------------------- |
| callback | callable | A callback that returns the data to be shared  |
| group    | string   | The group to use for the deferred data request |

```php
Inertia::defer(callable $callback, ?string $group = "default");
```

See the [Official InertiaJS website](https://inertiajs.com/deferred-props) for more details

## Merge

Normally, a reload request will overwrite all of the page props with fresh data. However, if you're trying to create an effect like infinite scrolling, you may want to add the new data to the old.

```php
Inertia::merge(mixed $value);
```

You can also combine merged props with defer:

```php
// controller handler method
$validated = $this->validated();
$page = $validated['page'] ?? 1;
$perPage = $validated['per_page'] ?? 10;

return $this->render('users/index', [
    'results' => Inertia::defer(
        fn() => SomeClass::getPaginationFunction($page, $perPage)
    )->merge(),
]);
```

### Prepending

By default, new items are added to the end. Use `prepend()` to add them to the start instead:

```php
'messages' => Inertia::merge($messages)->prepend(),
```

### Nested paths

When the prop is an object, such as a paginated result, merge into a key inside it rather than the whole prop. The rest of the object (e.g. pagination info) is replaced as normal:

```php
'results' => Inertia::merge([
    'data' => $posts,
    'total' => $total,
])->append('data'),
```

`append()` and `prepend()` accept a path, a list of paths (`['data', 'items']`) or paths mapped to a match key (`['data' => 'id']`), and can be combined on the same prop.

### Matching items

To update existing items instead of duplicating them, tell the client which key identifies an item. Pass it as the second argument to `append()`/`prepend()`, or use `matchOn()`:

```php
'results' => Inertia::merge(['data' => $posts])->append('data', 'id'),

'users' => Inertia::merge($users)->matchOn('id'),
```

### Deep merging

`deepMerge` recursively merges objects on the client instead of appending to a list. It's also available on deferred props as `->deepMerge()`:

```php
Inertia::deepMerge(mixed $value);

'settings' => Inertia::defer(fn () => $settings)->deepMerge(),
```

A merge prop is not merged when the client requests a reset of it, e.g. via `router.reload({ reset: ['results'] })`.

See the [Official InertiaJS website](https://inertiajs.com/merging-props) for more details

## Always

A prop wrapped in `always` is sent as part of the response no matter what.

By default, `errors` and `flash` are always props provided by the adapter.

```php
Inertia::always(mixed $value);
```

## Once

A once prop is resolved and sent on the first visit, then cached by the client. On later visits the client tells the server which once props it already has, and the server skips resolving them, which is ideal for data that rarely changes, like a list of countries or site settings.

```php
Inertia::once(callable $callback);

return $this->render('checkout', [
    'countries' => Inertia::once(fn () => get_countries()),
]);
```

Share a once prop with every page using `shareOnce`. It returns the prop so it can be configured further:

```php
Inertia::shareOnce('settings', fn () => get_theme_settings());
```

### Options

| Method                       | Description                                                                                          |
| ---------------------------- | ---------------------------------------------------------------------------------------------------- |
| `->until($delay)`            | Expire the client's cached copy after a number of seconds, a `DateInterval`, or at a `DateTime`       |
| `->as($key)`                 | Cache under a custom key (a string or enum), e.g. to share one cached value between pages             |
| `->fresh(bool $value = true)` | Resolve and send the prop on this visit even if the client already has it                            |

```php
'countries' => Inertia::once(fn () => get_countries())->until(HOUR_IN_SECONDS),

'plans' => Inertia::once(fn () => get_plans())->as('pricing-plans'),

'cart' => Inertia::once(fn () => get_cart())->fresh($cartWasUpdated),
```

Deferred, optional and merge props can also be resolved once:

```php
'stats' => Inertia::defer(fn () => get_stats())->once(),

// or with options
'stats' => Inertia::defer(fn () => get_stats())->once(as: 'dashboard-stats', until: 600),
```

A once prop is always sent on a full page load, and when it's explicitly requested in a partial reload.

See the [Official InertiaJS website](https://inertiajs.com/once-props) for more details

## Scroll

A scroll prop powers Inertia's `<InfiniteScroll>` component. It sends a page of results plus the pagination details the component needs to load the next (or previous) page, and new items are merged into the list as the visitor scrolls.

| Param    | Type                                      | Description                                                                                           |
| -------- | ----------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| value    | mixed                                     | The page of results, or a closure returning it, e.g. `['data' => $posts]`                              |
| wrapper  | string                                    | The key holding the list of items to merge (default `data`)                                            |
| metadata | ProvidesScrollMetadata\|callable\|null    | Pagination details, a callback given the resolved value that returns them, or `null` to use the main `WP_Query` |

```php
Inertia::scroll(mixed $value, string $wrapper = 'data', ProvidesScrollMetadata|callable|null $metadata = null);
```

On archive pages, the pagination comes from WordPress's main query, so no metadata is needed:

```php
// controllers/archive.php
return $this->render('Archive', [
    'posts' => Inertia::scroll(fn () => ['data' => Inertia::getArchive()->items]),
]);
```

```vue
<InfiniteScroll data="posts">
    <PostCard v-for="post in $page.props.posts.data" :key="post.id" :post="post" />
</InfiniteScroll>
```

For your own `WP_Query`, pass its pagination with `ScrollMetadata::fromQuery()`. The client requests further pages with the `paged` query parameter, which WordPress may redirect to a `/page/N/` URL, so read the page number with `get_query_var('paged')` rather than from `$_GET`:

```php
use EvoMark\InertiaWordpress\Data\ScrollMetadata;

$query = new WP_Query([
    'post_type' => 'event',
    'paged' => max(1, (int) get_query_var('paged')),
]);

return $this->render('Events', [
    'events' => Inertia::scroll(
        ['data' => array_map('my_event_to_array', $query->posts)],
        'data',
        ScrollMetadata::fromQuery($query)
    ),
]);
```

For anything else, pass your own pagination details: `new ScrollMetadata('page', $previousPage, $nextPage, $currentPage)`, or any class implementing `ProvidesScrollMetadata`.

Scroll props can be deferred with `->defer()` (optionally with a group name), so the first page loads straight after the initial visit.

See the [Official InertiaJS website](https://inertiajs.com/infinite-scroll) for more details

## Props Providers

A class implementing `ProvidesInertiaProperties` contributes several props at once, which makes a bundle of related props reusable across controllers. Add it to your props **without a key**, and each key it returns becomes a normal top-level prop:

```php
use EvoMark\InertiaWordpress\Contracts\ProvidesInertiaProperties;
use EvoMark\InertiaWordpress\Data\RenderContext;
use EvoMark\InertiaWordpress\Inertia;

class SeoProps implements ProvidesInertiaProperties
{
    public function toInertiaProperties(RenderContext $context): iterable
    {
        return [
            'seo' => get_seo_data(),
            'breadcrumbs' => Inertia::defer(fn () => get_breadcrumbs()),
        ];
    }
}

return $this->render('Post', [
    'post' => Inertia::getPost(),
    new SeoProps(),
]);
```

- `$context->component` is the component being rendered, and `$context->request` the current Inertia request handler, so one provider can return different props per page
- The props it returns can be any prop type (`defer`, `merge`, `once`, ...) and can be requested by name in partial reloads
- Keys listed after a provider override its props, and the provider overrides keys listed before it
- Providers can also be shared: `Inertia::share(new SeoProps())`, or inside an array passed to `share()`
- A provider, or any `Arrayable`, can also be passed as the entire props argument: `$this->render('Post', new SeoProps())`

`toInertiaProperties()` runs on every render, including partial reloads that don't ask for its props, so return closures or deferred props for anything expensive.

## Location

Redirect to an external URL.

If it's a standard response, this will be a header redirection.

If it's an Inertia response (JSON), then the frontend will handle the redirection for you.

```php
Inertia::location(string $url);
```

## Flash

Flashes data that will be included as part of the next page shown to the visitor, even after a redirect

| Param | Type                    | Description                                                                 |
| ----- | ----------------------- | --------------------------------------------------------------------------- |
| key   | string\|enum\|array     | The key to use, placed on `$page.flash[key]`, or an array of key => value pairs |
| value | mixed                   | Any serialisable value (omit when passing an array)                         |

```php
Inertia::flash(string|BackedEnum|UnitEnum|array $key, mixed $value = null);

Inertia::flash('success', 'Saved');
Inertia::flash(['success' => 'Saved', 'id' => 42]);
```

Multiple calls are merged. Flash data is also available on the deprecated `$page.props.flash`; see [Global Props](./global-props#flash).

A common usage would be providing feedback to a user after processing a POST request via the REST API.

```php
namespace MyAwesomeTheme\RestApi;

use WP_REST_Request;
use EvoMark\InertiaWordpress\Inertia;
use EvoWpRestRegistration\BaseRestController;

defined('ABSPATH') or exit;

class ExamplePost extends BaseRestController
{
    protected $path = 'example';
    protected $methods = 'POST';

    protected $rules = [
        'name' => ['required', 'string', 'max:50']
    ];

    public function authorise()
    {
        return true;
    }

    public function handler(WP_REST_Request $request)
    {
        $validated = $this->validated();

        // Do stuff

        Inertia::flash(
            "success",
            "Thanks {$validated['name']}, we've saved your data"
        );

        return Inertia::back();
    }
}
```

## Back

As shown [above](#flash), you cannot respond to a POST, PATCH, PUT, or DELETE request directly in Inertia.

You must redirect the user to a standard page. Often times, back to the page they were on before.

This will reload the page props with fresh data.

```php
Inertia::back();
```

:::tip
If the client made a partial reload request, the back redirection will only load the page props requested.
:::

## Back With Errors

Same as the [Back](#back) function, except you can manually throw a validation error and pass an associative array of errors to be returned to the frontend.

| Param   | Type                | Description                         |
| ------- | ------------------- | ----------------------------------- |
| request | \WP_REST_Request    | The current REST API request object |
| errors  | Array (associative) | An array of errors to return        |

### Example

```php
$user = wp_signon(apply_filters('woocommerce_login_credentials', $credentials), is_ssl());

if (is_wp_error($user) || !$validNonce) {
    Inertia::backWithErrors($request, [
        'username' => 'Could not login with these credentials',
    ]);
}
```

`exit` is automatically called after the response is sent

## Redirect

Returns a redirect response, useful if you want to send the user somewhere else after processing a form request.

```php
Inertia::redirect(get_post_type_archive_link( string $postType ));
```

## Get Archive

A helper function that gets the current archive along with paginated items.

Perfect for rendering your archive pages.

```php
Inertia::getArchive();
```

## Get Post

A helper function that gets the current (or provided) post.

The returned data includes featured images, comments, categories, tags et al.

```php
Inertia::getPost(?\WP_Post $post = null, array $args = null);
```

### Post Comments

Post comments will be on the `comments` prop of the post by default.

They will be paginated according to your Wordpress Discussion settings for the following:

- Break comments into pages (enable pagination)
- Top level comments per page (comments per page)
- Comments to display at the top of each page (comments order)

To change the comments page or override certain pagination values, add `comments_page`, `comments_per_page` and `comments_order` to your GET query string.

| GET param         | type                | default                    |
| ----------------- | ------------------- | -------------------------- |
| comments_page     | int                 | 1                          |
| comments_per_page | int                 | &lt;discussion setting&gt; |
| comments_order    | 'asc' &vert; 'desc' | &lt;discussion setting&gt; |

For example, with Vue:

```js
const commentsPage = ref(1);
const onNavigateComments = (page = 1) => {
	commentsPage.value = page;

	const url = new URL(window.location.href);
	url.searchParams.set("comments_page", commentsPage.value);
	router.get(
		url.toString(),
		{},
		{
			only: ["post"],
			replace: true,
			preserveState: true,
			preserveScroll: true,
		},
	);
};
```
