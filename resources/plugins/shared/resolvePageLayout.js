export const resolvePageLayout = (resolvedPage, layout, resolvedTemplate) => {
	let resolvedLayout = resolvedPage.default.layout || layout;
	if (!resolvedLayout) return null;

	resolvedLayout = Array.isArray(resolvedLayout) ? resolvedLayout : [resolvedLayout];
	resolvedLayout = resolvedLayout.filter((item) => item._is_template !== true);
	if (resolvedTemplate && resolvedLayout.includes(resolvedTemplate) === false) {
		resolvedLayout.push(resolvedTemplate);
	}
	return resolvedLayout.filter((item) => !!item);
};
