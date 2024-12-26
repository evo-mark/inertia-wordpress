# Server-Side Rendering (SSR)

When you're using JavaScript frameworks/libraries like Vue, React or Svelte for your application, the server will generally deliver a bare-bones HTML page (often with an empty `#app` element) and then send the JavaScript required to create the page.

Your framework of choice generates all the elements and then mounts to the `#app` element.

## The Downsides of Frameworks

In the past, search engines didn't used to execute this JavaScript and so they never saw the awesome content you were rendering for visitors.

This is less of a problem now, since most search engines will execute JavaScript and wait for data hydration when they're crawling a website. But some people prefer for the code to be there on first load, just in case.

## How SSR works

Server-Side Rendering is an extra step in the process made possible by a NodeJS server that holds a copy of your application and is able to render the full HTML page that the visitor requests.

1. Visitor requests a page of your site.
2. The server gets the HTML shell and is told to contact the SSR server for the rest.
3. Server contacts the SSR server, gives it the page props and gets the HTML code in return.
4. The generated code is then injected into the mount point.
5. Visitor gets the full HTML page immediately and the JavaScript needed to carry on navigating the rest of the website.

## Starting the SSR server

Once you've run a `build` of your theme, you can start the SSR server.

```sh
npm run ssr
```

This command uses a process manager called `PM2` to run our `wp inertia:start-ssr` command. Without PM2, you'd have to keep the command running in your terminal permanently. PM2 allows you to run it as a background process and even restarts the SSR server if your server has to restart.

:::warning
The SSR server will only be used if you've enabled SSR mode in your Wordpress admin menu. More on this in the next section.
:::

## Stopping the SSR server

```sh
npm run ssr:stop
```

## Restarting the SSR server

```sh
npm run ssr:restart
```

:::tip
All of the above commands must be run from your theme directory in your terminal.
:::
