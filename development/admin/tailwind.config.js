import Typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
	content: ["./js/**/*.{vue,js}"],
	theme: {
		container: {
			center: true,
			padding: {
				DEFAULT: "1rem",
				sm: "2rem",
				lg: "2rem",
				xl: "4rem",
				"2xl": "4rem",
			},
		},
		extend: {},
	},
	plugins: [Typography()],
};
