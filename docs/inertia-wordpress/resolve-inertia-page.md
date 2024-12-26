# resolveInertiaPage

A helper function for your frontend that does a lot of heavy lifting when it comes to resolving which page, layout and template to load, and how to load them.

Available for all frameworks (Vue, React and Svelte).

## Usage

The theme bootstrapper should have already added this to your `app.js(x)` and `ssr.js(x)` files.

::: code-group

```js [Vue]
import { createSSRApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolveInertiaPage } from "@evo-mark/inertia-wordpress/vue"; // [!code focus]

createInertiaApp({
  resolve: resolveInertiaPage(
    import.meta.glob("./pages/**/*.vue", { eager: false }),
  ),
  // ...
});
```

```jsx [React]
import { createInertiaApp } from "@inertiajs/react";
import { hydrateRoot, createRoot } from "react-dom/client";
import { resolveInertiaPage } from "@evo-mark/inertia-wordpress/react"; // [!code focus]

createInertiaApp({
  resolve: resolveInertiaPage(
    import.meta.glob("./pages/**/*.jsx", { eager: false }),
  ),
  // ...
});
```

```js [Svelte]
import { createInertiaApp } from "@inertiajs/svelte";
import { hydrate, mount } from "svelte";
import { resolveInertiaPage } from "@evo-mark/inertia-wordpress/svelte"; // [!code focus]

createInertiaApp({
  resolve: resolveInertiaPage(
    import.meta.glob("./pages/**/*.svelte", { eager: true }),
  ),
  // ...
});
```

:::

The function accepts three arguments:

## Arg #1: Page glob

> Required

This is the import meta glob of your page files. You can use either eager-loaded or lazy-loaded pages depending on your needs.

You can only `render` a page if it's included in this glob.

See the [Official InertiaJS website](https://inertiajs.com/code-splitting) for more details on code-splitting.

## Arg #2: Default Layout

> Optional

A layout component that automatically wraps the page unless the page itself overrides it.

Useful for default headers and headers that will be part of every page.

## Arg #3: Options Object

> Optional

An object that contains additional arguments for the function.

### layoutCallback

If you want a little more control over your default layout, you can provide a callback

```js
import Layout1 from './Layout';
import Layout2 from './Layout2';

// ...
resolve: resolveInertiaPage(
    import.meta.glob("./pages/**/*.vue", { eager: false }),
    null, // Notice that the 2nd argument is null
    { // Notice that this 3rd argument is an object
        /**
         * @param { string } name The page name that is being loaded
         * @param { VNode } resolvedPage The resolved page vNode
         * @param { ?VNode } resolvedTemplate If the post is assigned a template, the resolved vNode
         */
        layoutCallback:  (name, resolvedPage, resolvedTemplate) => {
            if (name.startsWith('account')) return Layout2;
            else return Layout;
        }
    }
),
```

### templates

Another glob that will be used for rendering a page template [see next section](/inertia-wordpress/templates)

```js
resolve: resolveInertiaPage(
    import.meta.glob("./pages/**/*.vue"),
    DefaultLayout,
    { // Notice that this 3rd argument is an object
        templates: import.meta.glob("./templates/**/*.vue") // [!code focus]
    }
),
```
