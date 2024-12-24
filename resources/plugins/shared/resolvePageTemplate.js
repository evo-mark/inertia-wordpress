export const resolvePageTemplate = async (templates, query, extension) => {
	if (!templates || !query) return null;

	const params = query ? new URLSearchParams(query) : null;
	const regex = extension ? new RegExp("\." + extension + "$") : "";
	const template = params ? atob(params.get("template")).replace(regex, "") : null;

	const resolvedTemplateKey = Object.keys(templates).find((key) => {
		return key.includes(template);
	});

	let resolvedTemplate = templates[resolvedTemplateKey] ?? null;
	resolvedTemplate =
		resolvedTemplate && typeof resolvedTemplate === "function" ? await resolvedTemplate() : resolvedTemplate;

	return resolvedTemplate?.default ?? resolvedTemplate;
};
