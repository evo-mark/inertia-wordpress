export const resolveInertiaPage = (
  glob,
  Layout = null,
  layoutCallback = null
) => {
  return async function (name) {
    let resolvedPage = glob[`./pages/${name}.jsx`];
    if (!resolvedPage) {
      console.error(`[Inertia] Couldn't find page matching "${name}"`);
      return null;
    }

    if (typeof resolvedPage === "function") {
      resolvedPage = await resolvedPage();
    }

    if (layoutCallback) {
      resolvedPage.default.layout = layoutCallback(name, resolvedPage);
    } else if (Layout) {
      resolvedPage.default.layout =
        resolvedPage.default.layout || ((page) => <Layout children={page} />);
    }

    return resolvedPage;
  };
};
