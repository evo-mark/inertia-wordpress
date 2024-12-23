<script>
	import { page } from "@inertiajs/svelte";

	let schema = $derived.by(() => {
		const s = $page.props.seo.schema;
		return `&lt;script type="application/ld+json">${JSON.stringify({
			"@context": "https://schema.org",
			"@graph": s,
		})}&lt;/script>`.replaceAll("&lt;", "<");
	});
</script>

<svelte:head>
	<title>{$page.props.seo.title}</title>
	{#each $page.props.seo.links as link}
		<link {...link} />
	{/each}
	{#each $page.props.seo.meta as meta}
		<meta {...meta} />
	{/each}
	{@html schema}
</svelte:head>
