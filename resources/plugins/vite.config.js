import { resolve } from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { svelte } from "@sveltejs/vite-plugin-svelte";

export default defineConfig({
	plugins: [vue(), svelte()],
	build: {
		lib: {
			entry: {
				vue: resolve(__dirname, "vue/index.js"),
				react: resolve(__dirname, "react/index.jsx"),
				svelte: resolve(__dirname, "svelte/index.js"),
			},
			formats: ["es"],
		},
		rollupOptions: {
			// make sure to externalize deps that shouldn't be bundled
			// into your library
			external: [
				"@vueuse/core",
				"@inertiajs/react",
				"@inertiajs/svelte",
				"@inertiajs/vue3",
				"react",
				"svelte",
				"vue",
			],
		},
	},
});
