import { resolvePageTemplate } from "../../shared/resolvePageTemplate";
import { resolvePageLayout } from "../../shared/resolvePageLayout";

export const resolveInertiaPage = (glob, layout = null, args = {}) => {
	if (typeof args === "function") {
		console.error(
			"Third argument to resolveInertiaPage must now be an object. Check documentation for full details",
		);
		return;
	}

	const { layoutCallback, templates } = args;

	return async function (name) {
		const [resolvedName, query] = name.split("?");
		const resolvedTemplate = await resolvePageTemplate(templates, query, "vue");

		let resolvedPage = glob[`./pages/${resolvedName}.vue`];
		if (!resolvedPage) {
			console.error(`[Inertia] Couldn't find page matching "${resolvedName}"`);
			return null;
		}

		if (typeof resolvedPage === "function") {
			resolvedPage = await resolvedPage();
		}

		if (layoutCallback) {
			resolvedPage.default.layout = layoutCallback(resolvedName, resolvedPage, resolvedTemplate);
		} else {
			resolvedPage.default.layout = resolvePageLayout(resolvedPage, layout, resolvedTemplate);
		}

		return resolvedPage;
	};
};
