# Hooks

Wordpress hooks are built-in functionality that allow the execution of custom code or modification of values at specific times during the Wordpress lifecycle.

## Usage

Rather than passing string values to `add_action` or `add_filter` to denote the hook name, it's recommended to import our constants file and pass the value defined inside. This allows your IDE to quickly tell you if you've made a typo and also avoids breaking changes in the unlikely event that we have to change the hook name.

```php
use EvoMark\InertiaWordpress\Helpers\HookFilters;

add_filter(HookFilters::ACF_SHARE, function($data) {
    return $data;
});
```

We will list both the constant key and the string key in our tables below.

## Actions

Used by calling `add_action(string $hookName, callable $callback, ?int $priority = 10, ?int $numberOfAcceptedArguments = 1): void`, this allows the execution of custom code. You are not allow to return a value.

See [Wordpress add_action function reference](https://developer.wordpress.org/reference/functions/add_action/) for more details.

### Actions used by Inertia Wordpress

```php
use EvoMark\InertiaWordpress\Helpers\HookActions;
```

> PRE_RENDER_ADMIN_BAR_UPDATE

**string key**: inertia_wordpress_pre_render_admin_bar_update

Fired before the admin bar update HTML elements are extracted. This hook is only fired on Inertia AJAX requests (i.e. not on first page requests or SSR requests)

> SET_GLOBAL_SHARES

**string key**: inertia_wordpress_set_global_shares

Called just before the shared props are returned by the request. Used internally, users should call `Inertia::share()`` instead.

> MODULES

**string key**: inertia_wordpress_modules

Hook for registering new Inertia Wordpress modules. Used internally, users should call `Inertia::addModule(string $module)` instead.

## Filters

Used by calling `add_filter(string $hookName, callable $callback, ?int $priority = 10, ?int $numberOfAcceptedArguments = 1): void`. The first argument passed to your callback can be modified and then returned. Failure to return a value in the expected format may break your site.

See [Wordpress add_filter function reference](https://developer.wordpress.org/reference/functions/add_filter/) for more details.

### Filters used by Inertia Wordpress

```php
use EvoMark\InertiaWordpress\Helpers\HookFilters;
```

> SHARE_MENU

**string name**: inertia_wordpress_share_menu

Called before each compiled menu object is passed to the frontend

| Arg Name    | Type     | Description                   |
| ----------- | -------- | ----------------------------- |
| $data       | array    | The compiled menu data object |
| $menuObject | \WP_Term | The menu object               |
| $menuId     | int      | The term ID of the menu       |

> SHARE_MENU_ITEMS_ARGS

**string name**: inertia_wordpress_share_menu_items_args

Filter the arguments supplied to each call to `wp_get_nav_menu_items` when generating menus

| Arg Name    | Type     | Description                                |
| ----------- | -------- | ------------------------------------------ |
| $args       | array    | The args passed to `wp_get_nav_menu_items` |
| $menuObject | \WP_Term | The menu object                            |

> MENU_ITEM

**string name**: inertia_wordpress_menu_item

Called when adding a menu item to the menus shared

| Arg Name  | Type     | Description                                 |
| --------- | -------- | ------------------------------------------- |
| $item     | stdClass | The generated menu item object              |
| $parentId | string   | The ID of the menu item's parent            |
| $rawItem  | \WP_Post | The original menu item class                |
| $menuId   | int      | The ID of the menu that the item belongs to |

> PAGE_TEMPLATE

**string name**: inertia_page_template

Selected page template before it's appended to the page URI

| Arg Name  | Type   | Description         |
| --------- | ------ | ------------------- |
| $template | string | The template slug   |
| $id       | int    | The current page ID |

> PAGE_CONTROLLER

**string name**: inertia_page_controller

Filter the resolved page controller

| Arg Name        | Type   | Description                                                    |
| --------------- | ------ | -------------------------------------------------------------- |
| $class          | string | The resolved controller class name that should be instantiated |
| $controllerFile | string | The absolute path of the controller file loaded                |

#### Modules

##### Advanced Custom Fields

> ACF_SHARE

| Arg Name | Type     | Description                                    |
| -------- | -------- | ---------------------------------------------- |
| $data    | stdClass | Modify the ACF fields shared with the frontend |

**string name**: inertia_modules_acf_share

> ACF_POST_FIELDS

**string name**: inertia_modules_acf_post_fields

| Arg Name | Type     | Description                                            |
| -------- | -------- | ------------------------------------------------------ |
| $data    | stdClass | Modify the ACF fields associated with the current post |

> ACF_OPTIONS_PAGES

**string name**: inertia_modules_acf_options_pages

| Arg Name | Type     | Description                                                     |
| -------- | -------- | --------------------------------------------------------------- |
| $data    | stdClass | Modify the ACF fields associated with any defined options pages |

## Feedback

If there's a hook you'd like added to the plugin, feel free to leave a feature request on our [GitHub Issues page](https://github.com/evo-mark/inertia-wordpress/issues).
