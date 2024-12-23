export const seoFrameworkExampleVue = `<template>
    <TheSeoFramework />
    <header>
        Your Website
    </header>
    <slot></slot>
    <footer>Footer stuff</footer>
</template>

<script setup>
// Your default layout

import { TheSeoFramework } from "@evo-mark/inertia-wordpress/vue";
</script>`;

export const seoFrameworkExampleReact = `import { TheSeoFramework } from "@evo-mark/inertia-wordpress/react";

function Layout({ children }) {
    return (
        <>
            <TheSeoFramework />
            <header></header>
            <main>{children}</main>
            <footer><footer>
        </>
    );
};`;

export const seoFrameworkExampleSvelte = `<script>
    import { page } from "@inertiajs/svelte";
    import { TheSeoFramework } from "@evo-mark/inertia-wordpress/svelte";

    let { children } = $props();
</script>

<TheSeoFramework />

<header></header>
<main>
    {@render children()}
</main>
<footer>
    <div>&copy; {new Date().getFullYear()} {$page.props.wp.name}</div>
</footer>
`;
