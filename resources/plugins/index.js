export const resolveInertiaPage = (glob, layout = null) => {
    return async function (name) {
        let resolvedPage = glob[`./pages/${name}.vue`];
        if (!resolvedPage) {
            console.error(`[Inertia] Couldn't find page matching "${name}"`);
            return null;
        }

        if (typeof resolvedPage === "function") {
            resolvedPage = await resolvedPage();
        }

        if (typeof layout === "function") {
            resolvedPage.default.layout = layout(name, resolvedPage);
        } else if (layout) {
            resolvedPage.default.layout = layout;
        }

        return resolvedPage;
    };
};
