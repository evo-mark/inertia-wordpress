import { resolvePageTemplate } from "../../shared/resolvePageTemplate";

export const resolveInertiaPage = (glob, Layout = null, args = {}) => {
	if (typeof args === "function") {
		console.error(
			"Third argument to resolveInertiaPage must now be an object. Check documentation for full details",
		);
		return;
	}

	const { layoutCallback, templates } = args;
	const templateMap = new WeakMap();

	return async function (name) {
		const [resolvedName, query] = name.split("?");
		const ResolvedTemplate = await resolvePageTemplate(templates, query, null);

		let resolvedPage = glob[`./pages/${resolvedName}.jsx`];
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
			let ResolvedLayout =
				resolvedPage.default.layout || ((page) => <Layout children={page?.children ?? page} />);

			if (ResolvedTemplate && !templateMap.get(ResolvedLayout)) {
				const OriginalLayout = ResolvedLayout;

				ResolvedLayout = (page) => {
					return (
						<OriginalLayout>
							<ResolvedTemplate children={page} />
						</OriginalLayout>
					);
				};
				templateMap.set(ResolvedLayout, true);
			}
			resolvedPage.default.layout = ResolvedLayout;
		}

		return resolvedPage;
	};
};
