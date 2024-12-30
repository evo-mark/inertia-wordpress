import { defineConfig } from "vitepress";
import { socialLinks } from "./helpers/socialLinks";
import { generateChangelogMenu } from "./helpers/generateChangelogMenu";
import { sidebar } from "./helpers/sidebar";
import { copyChangelogs } from "./helpers/copyChangelogs";

copyChangelogs();

// https://vitepress.dev/reference/site-config
export default defineConfig({
	title: "Inertia Wordpress",
	titleTemplate: ":title | Inertia Wordpress",
	description: "A community Wordpress adapter for InertiaJS",
	head: [
		["link", { rel: "icon", href: "/favicon-dark.png" }],
		[
			"script",
			{
				defer: true,
				src: "https://plausible.southcoastweb.co.uk/js/script.js",
				"data-domain": "inertia-wordpress.evomark.co.uk",
			},
		],
	],
	themeConfig: {
		logo: {
			light: "/logo-light.svg",
			dark: "/logo-dark.svg",
		},
		siteTitle: false,
		// https://vitepress.dev/reference/default-theme-config
		nav: [{ text: "Home", link: "/" }, generateChangelogMenu()],

		sidebar,

		socialLinks,

		footer: {
			message: "Released under the Apache2 License.",
			copyright: "Copyright © 2024 Evo Mark Ltd",
		},
		search: {
			provider: "algolia",
			options: {
				appId: "N038QDFYJO",
				apiKey: "71639db700323b989016c89d794c0889",
				indexName: "inertia-wordpress-evomark-co",
			},
		},
	},
});
