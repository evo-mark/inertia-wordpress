# Custom Modules

You can now add custom modules to the Inertia Wordpress adapter.

## Extending the BaseModule

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

## Registration

You can then register your module by doing:

```php
use EvoMark\InertiaWordpress\Inertia;
use YourProject\YourModule;

add_action('inertia_wordpress_modules', function () {
    Inertia::addModule(YourModule::class);
});
```

For extra convenience, you can even just call

```php
Inertia::addModule(YourModule::class);
```

without the `add_action` part and Inertia Wordpress will automatically add the module at the correct time.

Don't forget to enable your module in the `Inertia -> Settings` menu once it is registering.

## Lifecycle

1. Plugin boots
2. All modules are added to the registry
3. Checks that the `$class` for the plugin exists
4. Checks that plugin is activated
5. Checks that the module is enabled
6. If all checks pass, the module's `register` method is called
7. Request is handled through the controller
8. `boot` method is called just before a response is sent
