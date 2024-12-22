import { resolve } from "path";
import { defineConfig } from "vite";

export default defineConfig({
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
      external: ["vue", "@inertiajs/vue3", "@vueuse/core"],
      output: {
        // Provide global variables to use in the UMD build
        // for externalized deps
        globals: {
          vue: "Vue",
        },
      },
    },
  },
});
