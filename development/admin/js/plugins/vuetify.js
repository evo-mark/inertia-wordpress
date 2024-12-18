import { createVuetify } from "vuetify";
import { aliases, mdi } from "vuetify/iconsets/mdi-svg";
import "vuetify/styles";

export default createVuetify({
	locale: {
		locale: "en",
	},
	date: {
		locale: {
			en: "en-GB",
		},
	},
	icons: {
		defaultSet: "mdi",
		aliases,
		sets: {
			mdi,
		},
	},
	defaults: {
		VTextField: {
			variant: "solo",
			class: "max-w-prose",
		},
		VTextarea: {
			variant: "solo",
			class: "max-w-prose",
		},
		VSelect: {
			variant: "solo",
			class: "max-w-prose",
		},
		VSwitch: {
			color: "primary",
		},
		VAlert: {
			border: "start",
			variant: "tonal",
		},
	},
});
