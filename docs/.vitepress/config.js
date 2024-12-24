import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "Inertia Wordpress",
  description: "A community Wordpress adapter for InertiaJS",
  themeConfig: {
    logo: "logo.svg",
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: "Home", link: "/" },
      {
        text: 123,
        items: [
          {
            text: "Changelog",
            link: "https://github.com/vuejs/vitepress/blob/main/CHANGELOG.md",
          },
          {
            text: "Contributing",
            link: "https://github.com/vuejs/vitepress/blob/main/.github/contributing.md",
          },
        ],
      },
    ],

    sidebar: [
      {
        text: "Examples",
        items: [
          { text: "Get Started", link: "/get-started" },
          { text: "Runtime API Examples", link: "/api-examples" },
        ],
      },
    ],

    socialLinks: [
      { icon: "github", link: "https://github.com/evo-mark/inertia-wordpress" },
    ],
  },
});
