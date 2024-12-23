<p align="center">
    <a href="https://evomark.co.uk" target="_blank" alt="Link to evoMark's website">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--dark.svg">
          <source media="(prefers-color-scheme: light)" srcset="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--light.svg">
          <img alt="evoMark company logo" src="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--light.svg" width="500">
        </picture>
    </a>
</p>


# WP Vite
Tags: wordpress, vite, esbuild, development, production, build
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2
License: MIT
License URI: https://mit-license.org/

Bring Vite's lightning fast build process to your Wordpress theme or plugin

## Usage

Inside your plugin entry file or function.php file (for themes) simply include the following:


    use EvoMark\WpVite\WpVite;

    $vite = new WpVite;
    $vite->enqueue([
        'namespace' => 'theme-vite',
        'input' => ["src/main.js"],
    ]);


The enqueue function takes a single associative array as a parameter. Here are the properties it can contain:

| arg | type | required | description |
| --- | ---- | -------- | ----------- |
| namespace | string | true | A unique namespace for the manifest being enqueued |
| input | string\|string[] | true | One or more entry files. These must match exactly the ones defined in your Vite config file |
| dependencies | string[] | false | Wordpress JS dependencies for your manifest. In production, these will be mapped to the window object |
| admin | bool | false | Enqueue the inputs for Wordpress admin pages instead of frontend |
| absolutePath | string | false | Override the absolute path of your build folder |
| absoluteUrl | string | false | Override the absolute URL of your build folder | 
| buildDirectory | string | false | Override the name of your build subfolder (default 'build') |
| priority | int | false | Set the Wordpress priority of your script(s) |

## Frontend

You will require the [Wordpress Vite Plugin](https://www.npmjs.com/package/wordpress-vite-plugin) installed as part of your build process. See link for installation instructions

## Frequently Asked Questions

- Does this work with Gutenberg block development?

    No. This plugin currently does not support usage in the development of block libraries. For that, we recommend [Vite Plugin Gutenberg Blocks](https://github.com/evo-mark/vite-plugin-gutenberg-blocks).
