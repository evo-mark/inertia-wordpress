import { createApp } from "vue";
import { vuetify } from "./plugins";
import router from "./router";
import DefaultLayout from "./layouts/Default.vue";
import { createHead } from "@unhead/vue";
import { SnackbarService } from "vue3-snackbar";
import "../css/style.postcss";

const head = createHead();

createApp(DefaultLayout)
    .use(head)
    .use(router)
    .use(vuetify)
    .use(SnackbarService)
    .mount("#inertia-wordpress__admin-page--wrapper");
