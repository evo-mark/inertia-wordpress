# Global Props

As you've probably noticed, Inertia is all about the page props.

While you can share anything you want with your frontend, we've also provided some global props to help make your life easier.

## Errors

> $page.props.errors

This is an object that's used by your InertiaJS [Form Helper](https://inertiajs.com/forms#form-helper). If you're using our recommended REST API controllers for processing forms, any validation errors will be automatically returned with this object.

## Flash

> $page.flash

Flash data is short-lived information passed to your frontend that provides feedback on user actions.

This can be used for, say, showing a user a snackbar/toast message when a form has been submitted.

```php
use EvoMark\InertiaWordpress\Inertia;

Inertia::flash('success', 'Thanks for your message');
```

Flash data is a top-level key on the page object rather than a prop, as in Inertia v2. The client keeps it out of browser history (so a toast doesn't reappear on back/forward) and fires a `flash` event when it arrives:

```js
import { router, usePage } from "@inertiajs/vue3";

// Read it directly...
const flash = usePage().flash;

// ...or react whenever new flash data arrives
router.on("flash", (event) => {
    showToast(event.detail.flash.success);
});
```

::: warning Deprecated: `$page.props.flash`
Flash data is also still shared as `$page.props.flash` for backwards compatibility. This will be removed in a future release, so move to `$page.flash` or the `flash` event.
:::

## WP

> $page.props.wp

Common data from your Wordpress application that might be useful in various situations.

Some examples include (but are not limited to):

| Prop                | Description                                                                                 |
| ------------------- | ------------------------------------------------------------------------------------------- |
| wp.name             | The name of your site                                                                       |
| wp.homeUrl          | The URL of your site's homepage                                                             |
| wp.restUrl          | The base URL for making REST API requests                                                   |
| wp.user             | The user object for the currently logged in user                                            |
| wp.userCapabilities | An object of the current users capabilities/permission                                      |
| wp.logo             | An image resource containing your site logo as set in Wordpress' Appearance->Customise menu |
| wp.menus            | A nested object containing your registered menus, keyed by location                         |
| wp.adminBar         | An array of changed HTML elements that can be updated in your admin bar                     |
| wp.nonces           | Common nonces (number used once) that are sometimes needed for making requests              |
