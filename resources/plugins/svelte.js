export const resolveInertiaPage = (
  glob,
  layout = null,
  layoutCallback = null
) => {
  return async function (name) {
    let resolvedPage = glob[`./pages/${name}.svelte`];
    if (!resolvedPage) {
      console.error(`[Inertia] Couldn't find page matching "${name}"`);
      return null;
    }

    if (typeof resolvedPage === "function") {
      resolvedPage = await resolvedPage();
    }

    if (layoutCallback) {
      return {
        default: resolvedPage.default,
        layout: layoutCallback(name, resolvedPage),
      };
    } else {
      return {
        default: resolvedPage.default,
        layout: resolvedPage.layout || layout,
      };
    }
  };
};
