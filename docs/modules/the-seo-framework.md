# The SEO Framework

:::tip
You must enable this module from the [Wordpress Settings](/inertia-wordpress/settings) menu to receive data
:::

The SEO Framework is an expert system for SEO. It is a solution that can intelligently generate
critical SEO meta tags in any language by reading your WordPress environment. This automation
saves you a considerable amount of time that could be used to write more content or focus on
other tasks. It also removes the need for advanced SEO knowledge.

## Module Features

- Compiles SEO links, meta tags and JSON schema
- Prevents the SEO tags from being directly written to the page
- Redirects the tags to `$page.props.seo`
- Helper components for all frameworks

## Helper Component

As mentioned, Inertia Wordpress also provides a helper component for all frameworks to bring full SEO Framework integration to your site in just two lines of code.

Since all of the SEO props are managed through your page props, you will likely only need to use the component in a single place. Your default layout is an excellent location for this.

::: code-group

```vue [Vue]
<template>
  <TheSeoFramework />
  <header>Your Website</header>
  <slot></slot>
  <footer>Footer stuff</footer>
</template>

<script setup>
// Your default layout

import { TheSeoFramework } from "@evo-mark/inertia-wordpress/vue";
</script>
```

```jsx [React]
import { TheSeoFramework } from "@evo-mark/inertia-wordpress/react";

function Layout({ children }) {
    return (
        <>
            <TheSeoFramework />
            <header></header>
            <main>{children}</main>
            <footer><footer>
        </>
    );
}
```

```js [Svelte]
<script>
    import { page } from "@inertiajs/svelte";
    import { TheSeoFramework } from "@evo-mark/inertia-wordpress/svelte";

    let { children } = $props();
</script>

<TheSeoFramework />

<header></header>
<main>
    {@render children()}
</main>
<footer>
    <div>&copy; {new Date().getFullYear()} {$page.props.wp.name}</div>
</footer>
```

:::
