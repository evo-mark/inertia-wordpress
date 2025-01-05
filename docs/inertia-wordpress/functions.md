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

## Always

A prop wrapped in `always` is sent as part of the response no matter what.

By default, `errors` and `flash` are always props provided by the adapter.

```php
Inertia::always(mixed $value);
```

## Location

Redirect to an external URL.

If it's a standard response, this will be a header redirection.

If it's an Inertia response (JSON), then the frontend will handle the redirection for you.

```php
Inertia::location(string $url);
```

## Flash

Flashes data that will be including as part of the next response

| Param | Type   | Description                                                |
| ----- | ------ | ---------------------------------------------------------- |
| key   | string | The key to use, will be placed on `$page.props.flash[key]` |
| value | mixed  | Any serialisable value                                     |

```php
Inertia::flash(string $key, mixed $value);
```

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

> Post Comments

Post comments will be on the `comments` prop of the post by default. They will be paginated and capped at 10 per page. To change the comments page or per page values, add `comments_page` and `comments_per_page` to your GET query string.

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
