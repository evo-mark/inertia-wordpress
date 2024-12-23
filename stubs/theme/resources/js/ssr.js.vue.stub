import { createSSRApp, h } from "vue";
import createServer from "@inertiajs/vue3/server";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolveInertiaPage } from "@evo-mark/inertia-wordpress";
import { renderToString } from "@vue/server-renderer";
import DefaultLayout from "./layouts/DefaultLayout.vue";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: resolveInertiaPage(
            import.meta.glob("./pages/**/*.vue", { eager: true }),
            DefaultLayout
        ),
        setup({ App, props, plugin }) {
            return createSSRApp({
                render: () => h(App, props),
            }).use(plugin);
        },
    })
);
