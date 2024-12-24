export const resolvePageLayout = (resolvedPage, layout, resolvedTemplate) => {
	let resolvedLayout = resolvedPage.default.layout || layout;
	resolvedLayout = Array.isArray(resolvedLayout) ? resolvedLayout : [resolvedLayout];
	if (resolvedTemplate && resolvedLayout.includes(resolvedTemplate) === false) {
		resolvedLayout.push(resolvedTemplate);
	}
	return resolvedLayout.filter((item) => !!item);
};
