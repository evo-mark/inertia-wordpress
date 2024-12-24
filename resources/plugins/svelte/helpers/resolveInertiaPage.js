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
		const resolvedTemplate = await resolvePageTemplate(templates, query, "svelte");

		let resolvedPage = glob[`./pages/${resolvedName}.svelte`];
		if (!resolvedPage) {
			console.error(`[Inertia] Couldn't find page matching "${resolvedName}"`);
			return null;
		}

		if (typeof resolvedPage === "function") {
			resolvedPage = await resolvedPage();
		}

		if (layoutCallback) {
			return {
				default: resolvedPage.default,
				layout: layoutCallback(resolvedName, resolvedPage, resolvedTemplate),
			};
		} else {
			return {
				default: resolvedPage.default,
				layout: resolvePageLayout(resolvedPage, layout, resolvedTemplate),
			};
		}
	};
};
