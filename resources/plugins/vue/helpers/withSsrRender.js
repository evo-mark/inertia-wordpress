/**
 * Wraps the options passed to `createInertiaApp` in an SSR entry so that the page renders
 * correctly with Vue 3.5.44+.
 *
 * `@inertiajs/vue3` 2.x renders the page app synchronously inside its wrapper element
 * (`innerHTML: render(vueApp)`), relying on Vue's server renderer to await the Promise
 * `renderToString` returns. Since Vue 3.5.44 that Promise is stringified instead, so the
 * server sends `[object Promise]` and the client reports a hydration mismatch.
 *
 * This renders the page app up front, then hands Inertia the finished HTML for its inner
 * call. The page is still rendered only once, and it works on older Vue versions too.
 *
 * Call it once per request, inside the `createServer` callback:
 *
 *   createServer((page) =>
 *       createInertiaApp(withSsrRender({ page, render: renderToString, resolve, setup }))
 *   );
 *
 * @param {object} options The `createInertiaApp` options, including `render` and `setup`
 * @returns {object} The same options with `render` and `setup` wrapped
 */
export const withSsrRender = (options) => {
	const { render, setup } = options;

	if (typeof render !== "function" || typeof setup !== "function") {
		throw new Error("[Inertia] withSsrRender requires both `render` and `setup` options");
	}

	let vueApp = null;
	let appHtml = "";

	return {
		...options,
		setup(args) {
			vueApp = setup(args);
			return vueApp;
		},
		render(app) {
			// Inertia's synchronous inner call for the page app: return the HTML rendered below.
			if (vueApp && app === vueApp) {
				return appHtml;
			}

			// Inertia's outer call for its wrapper element: render the page app first.
			return Promise.resolve(vueApp ? render(vueApp) : "").then((html) => {
				appHtml = html;
				return render(app);
			});
		},
	};
};
