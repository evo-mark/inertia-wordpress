<p align="center">
    <a href="https://evomark.co.uk" target="_blank" alt="Link to evoMark's website">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--dark.svg">
          <source media="(prefers-color-scheme: light)" srcset="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--light.svg">
          <img alt="evoMark company logo" src="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--light.svg" width="500">
        </picture>
    </a>
</p>

# Inertia Wordpress

<p align="center">
    <a href="https://github.com/evo-mark/inertia-wordpress/releases">Get the Latest Release</a>
</p>

Inertia is a new approach to building classic server-driven web apps. We call it the modern monolith.

Inertia allows you to create fully client-side rendered, single-page apps, without the complexity that comes with modern SPAs. It does this by leveraging existing server-side patterns that you already love.

This is an unofficial adapter for applications powered by Wordpress that allows you to create a reactive JavaScript frontend.

## Installation

Download the <a href="https://github.com/evo-mark/inertia-wordpress/releases">inertia-wordpress.zip</a> file from the latest release to get started. Updates will then be handled automatically through Wordpress.

## Requirements

- NodeJS 20+
- Composer
- WP-CLI

## Getting Started

From the root of your Wordpress application, run the following command to bootstrap a new InertiaJS theme

```sh
wp inertia:create-theme
```

> Currently, only Vue is supported, but more will be coming soon.
