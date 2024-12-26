# Development

If you've [used the theme bootstrapper](/getting-started/creating-a-theme), then you should be ready-to-go with expanding your theme.

## A Brief Introduction to Vite

Vite is a build-tool/bundler that takes all of your development files and creates browser-compatible JavaScript and CSS.

It also provides "Hot Module Reloading" (HMR) in development mode that updates your browser page in real-time (without causing a page refresh) when you make changes to your code.

At evoMark, we've created some tools that make using Vite with Wordpress a breeze. These will be already installed for you, but if you're interested, they are:

- [WP Vite](https://github.com/evo-mark/wp-vite/) - A PHP package that brings Vite to theme and package development
- [Wordpress Vite Plugin](https://github.com/evo-mark/wordpress-vite-plugin) - The JavaScript companion package for frontend

## Starting the Dev Server

From your theme directory, run the `dev` script and your development server will start.

![Theme dev server](/screenshots/theme-dev-mode.png)

In your browser, go to your Wordpress site and you should see the start of your adventure in Inertia Wordpress.

::: tip
The `Local` URL shown above is the URL of the dev server itself. You will _not_ be able to see your site at this address.
:::
