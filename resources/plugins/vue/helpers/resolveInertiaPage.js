export const resolveInertiaPage = (
  glob,
  layout = null,
  layoutCallback = null
) => {
  return async function (name) {
    let resolvedPage = glob[`./pages/${name}.vue`];
    if (!resolvedPage) {
      console.error(`[Inertia] Couldn't find page matching "${name}"`);
      return null;
    }

    if (typeof resolvedPage === "function") {
      resolvedPage = await resolvedPage();
    }

    if (layoutCallback) {
      resolvedPage.default.layout = layoutCallback(name, resolvedPage);
    } else if (layout) {
      resolvedPage.default.layout = resolvedPage.default.layout || layout;
    }

    return resolvedPage;
  };
};
