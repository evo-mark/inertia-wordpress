# What's in a Theme?

Your newly bootstrapped theme should look similar to the below structure.

On this page, we'll do a quick breakdown of what each file/directory's purpose is.

```
├── app.php
├── composer.json
├── controllers
│ ├── archive.php
│ ├── page.php
│ └── single.php
├── ecosystem.config.cjs
├── functions.php
├── index.php
├── package.json
├── resources
│ ├── css
│ │ └── style.postcss
│ └── js
│ ├── main.js
│ ├── pages
│ │ └── Archive.vue
│ │ ├── Home.vue
│ │ ├── Post.vue
│ └── ssr.js
├── rest-api
│ └── TestPost.php
├── style.css
└── vite.config.js
```

## app.php

This is Inertia's entry point for your theme. Feel free to add anything you need, but remember that the HTML will only load on the first request.

See [InertiaJS.com > Server-Side Setup > Root Template](https://inertiajs.com/server-side-setup#root-template) for more information.

## controllers

These files will be the bridge between Wordpress and Inertia for serving GET requests.

Each file should contain a single class that:

1. Extends the `EvoMark\InertiaWordpress\InertiaController` class.
2. Provides a `handle` method.
3. The handle method must return a call to `$this->render()`.

```php
// controllers/page.php
<?php

namespace MyAwesomeTheme;

use EvoMark\InertiaWordpress\InertiaController;

class Page extends InertiaController
{
    public function handle()
    {
        return $this->render("Home", [
            // Add your page props here
        ]);
    }
}
```

The files in this directory are loaded in accordance with [Wordpress' template hierarchy rules](https://developer.wordpress.org/themes/basics/template-hierarchy/), albeit moved from the root of your theme to this `controllers` directory.

Remember that these classes will execute in SSR, standard and Inertia response contexts, so avoid using `echo` or relying on Wordpress hooks and filters.

::: info
Controllers are called via short-circuiting the `template_include` filter and call `wp_head` and `wp_footer`.
:::

## ecosystem.config.cjs

This is an optional file provided to make running your InertiaJS SSR process easier. It is read by the PM2 process manager and keeps your process running reliably.

For more information see the page on [PM2 Ecosystem Files](https://pm2.keymetrics.io/docs/usage/application-declaration/)

## functions.php

The standard entry point for your theme. The bootstrapper will populate this with a small amount of code.

The code `new RestApi` is a call to [WP Rest Registration](https://evomark.co.uk/open-source-software/wp-rest-registration/) which allows you to easily register REST API endpoints that can validate input.

We'll be using these endpoints to process information submitted by our InertiaJS frontend. See `rest-api` below for more details.

## index.php

Intentionally left empty to prevent directory indexing

## package.json

A file used by NodeJS package managers to install dependencies required by your theme. Common package managers are `NPM`, `PNPM`, and `Yarn`.

## resources

This is where the majority of your themes frontend will live. It's no different from a standard InertiaJS project setup, so we won't go into much detail on the file structure.

## rest-api

Since InertiaJS sends its data via AJAX requests, we can't use our `controllers` classes to process it. Instead, we must use special REST API controllers.

All classes in this directory are automatically registered by the call the `new RestApi` in your `functions.php` file. The structure of these files should be familiar to anyone experienced with Laravel Controllers.

See the [WP REST Registration documentation](https://evomark.co.uk/open-source-software/wp-rest-registration/) for more details.

## style.css

Required by Wordpress.

## vite.config.js

Used by Vite to bundle/build your theme's JavaScript and CSS files. You shouldn't need to change anything here to get Vite working, but feel free to add more plugins.
