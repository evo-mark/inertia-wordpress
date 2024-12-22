- **Feature**: New Contact Form 7 module
- **Feature** New `Inertia::flash($key, $value)` method for sending flash data to the frontend
- **Feature**: New module pages for ACF and Contact Form 7
- **Feature**: New module registration feature available via `Inertia::addModule($class)` (see below);
- **Improvement**: Scope flash data to users by a temporary ID to prevent collisions
- **Improvement**: Added free version of ACF as a valid entry file for the module
- **BugFix**: Revised package.json dependency for @evo-mark/inertia-wordpress to a `file` install

## Breaking Change

In your theme's `package.json`, change the dependency for `@evo-mark/inertia-wordpress` to the following:

```
 "@evo-mark/inertia-wordpress": "file:../../plugins/inertia-wordpress/resources/plugins",
```

and then run install from your package manager again.

## Custom Modules

You can now add custom modules to the Inertia Wordpress adapter.

First create a class like so:

```php
namespace YourProject;

use EvoMark\InertiaWordpress\Inertia;
use EvoMark\InertiaWordpress\Modules\BaseModule;

class YourModule extends BaseModule
{
    // The title of the module to be displayed
    protected string $title = "Advanced Custom Fields";

    // Optional URI for a module logo
    protected string $logo;

    // The main class of the plugin that the module interacts with
    protected string $class = "ACF";

    // Internal reference, alpha-numeric and lowercase
    protected string $slug = "acf";

    // Any valid entry files for the plugin relative to the wp-content/plugins directory.
    protected array|string $entry = ['advanced-custom-fields-pro/acf.php', 'acf-pro/acf.php'];

    /**
     * Called immediately if the module is enabled and plugin installed/activated
     */
    public function register()
    {
        //
    }

    /**
     * Called before shared props are returned
     */
    public function boot(): void
    {
        Inertia::share('myModule', [
            // My data
        ]);
    }
}
```

You can then register your module by doing:

```php
use EvoMark\InertiaWordpress\Inertia;
use YourProject\YourModule;

add_action('inertia_wordpress_modules', function () {
    Inertia::addModule(YourModule::class);
});
```

Don't forget to enable your module in the `Inertia -> Settings` menu once it is registering.
