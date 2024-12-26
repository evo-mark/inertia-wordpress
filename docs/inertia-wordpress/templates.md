# Templates

Templates are a feature built into Wordpress that allow you to wrap page content with some common layout. Think of them as "sub-layouts".

More information is available on [wordpress.org](https://developer.wordpress.org/themes/templates/templates/)

:::tip
Templates do not override your layout and will be added inside any layouts before the page is added.
:::

By default, Inertia Wordpress expects your templates to be in a directory at `resources/js/templates` (although this can be changed in the settings menu). So start by creating this directory.

## Using Templates

Ensure you've [followed the step](/inertia-wordpress/resolve-inertia-page.html#arg-3-options-object) on adding your `templates` glob to the build.

Wordpress will add any templates it finds to the available options in the block editor.

![Wordpress templates](/screenshots/wordpress-templates.png)

## Full Steps

1. Create a new folder for your templates, such as `resources/js/templates`
2. If you use something different, make sure you change the setting in your [Wordpress settings menu](/inertia-wordpress/settings)
3. Create your templates, making sure you follow [your framework's instructions](https://inertiajs.com/pages#creating-layouts) on layouts
4. Wordpress will now read your templates folder and make them available as templates in the admin block editor
5. Pass your templates directory as an import glob to `resolveInertiaPage` in your `app.js` and `ssr.js` files
