import AdvancedCustomFields from "../pages/modules/AdvancedCustomFields.vue";
import ContactForm7 from "../pages/modules/ContactForm7.vue";
import NinjaForms from "../pages/modules/NinjaForms.vue";
import TheSeoFramework from "../pages/modules/TheSeoFramework.vue";
import WooCommerce from "../pages/modules/WooCommerce.vue";
import SettingsPage from "../pages/Settings.vue";
import ModulesPage from "../pages/modules/index.vue";
import WebPExpress from "../pages/modules/WebPExpress.vue";

export default [
	{
		path: "/",
		component: SettingsPage,
		meta: {
			title: "Inertia Settings",
		},
		name: "settings",
	},
	{
		path: "/modules",
		component: ModulesPage,
		meta: {
			title: "Modules",
		},
		name: "modules",
	},
	{
		path: "/modules/acf",
		component: AdvancedCustomFields,
		meta: {
			title: "Modules: Advanced Custom Fields",
		},
		name: "modules.acf",
	},
	{
		path: "/modules/cf7",
		component: ContactForm7,
		meta: {
			title: "Modules: Contact Form 7",
		},
		name: "modules.cf7",
	},
	{
		path: "/modules/ninja-forms",
		component: NinjaForms,
		meta: {
			title: "Modules: Ninja Forms",
		},
		name: "modules.ninja-forms",
	},
	{
		path: "/modules/webp-express",
		component: WebPExpress,
		meta: {
			title: "Modules: WebP Express",
		},
		name: "modules.webp-express",
	},
	{
		path: "/modules/seo-framework",
		component: TheSeoFramework,
		meta: {
			title: "Modules: The SEO Framework",
		},
		name: "modules.seo-framework",
	},
	{
		path: "/modules/woocommerce",
		component: WooCommerce,
		meta: {
			title: "Modules: WooCommerce",
		},
		name: "modules.woocommerce",
	},
];
