import { defineConfig, loadEnv } from "vite";
import basicSsl from "@vitejs/plugin-basic-ssl";
import vue from "@vitejs/plugin-vue";
import vuetify from "vite-plugin-vuetify";
import { resolve } from "node:path";
import AutoImport from "unplugin-auto-import/vite";
import { wordpress } from "wordpress-vite-plugin";

export default ({ mode }) => {
	const env = loadEnv(mode, resolve(process.cwd() + "/../../"), "VITE");

	return defineConfig({
		css: {
			preprocessorMaxWorkers: true,
			preprocessorOptions: {
				scss: {
					api: "modern-compiler",
				},
				sass: {
					api: "modern-compiler",
				},
			},
		},
		server: {
			host: env.VITE_DEV_SERVER ?? "127.0.0.1",
		},
		resolve: {
			alias: {
				components: resolve("./js/components"),
				composables: resolve("./js/composables"),
				helpers: resolve("./js/helpers"),
				images: resolve("./images"),
				queries: resolve("./js/queries"),
				layouts: resolve("./js/layouts"),
				pages: resolve("./js/pages"),
				plugins: resolve("./js/plugins"),
				sublayouts: resolve("./js/layouts/nested"),
			},
		},
		plugins: [
			env.VITE_DEV_HTTPS === "basic" ? basicSsl() : undefined,
			vue({
				template: {
					transformAssetUrls: {
						base: null,
						includeAbsolute: false,
					},
				},
			}),
			wordpress({
				input: ["js/main.js"],
				namespace: "inertia-wordpress-admin",
				publicDirectory: `../../build`,
				buildDirectory: "admin",
			}),
			AutoImport({
				imports: ["vue", "vue-router"],
				eslintrc: {
					enabled: true,
					filepath: "./eslint-auto-import.json",
					globalsPropValue: true,
				},
				dirs: ["./js/composables"],
				viteOptimizeDeps: false,
			}),
			vuetify({
				autoImport: true,
				styles: {
					configFile: "js/plugins/vuetify-settings.scss",
				},
			}),
		],
	});
};
