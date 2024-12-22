import AdvancedCustomFields from "../pages/modules/AdvancedCustomFields.vue";
import ContactForm7 from "../pages/modules/ContactForm7.vue";
import SettingsPage from "../pages/Settings.vue";

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
];
