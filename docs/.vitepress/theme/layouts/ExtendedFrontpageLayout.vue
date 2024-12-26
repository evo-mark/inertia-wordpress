<template>
  <Layout>
    <template #home-features-before>
      <div ref="container" class="front-beam-container">
        <AnimatedBeam
          :container-ref="containerRef"
          :from-ref="wordpressRef"
          :to-ref="inertiaRef"
          :curvature="30"
          :start-y-offset="10"
          :end-y-offset="0"
        />
        <AnimatedBeam
          :container-ref="containerRef"
          :from-ref="inertiaRef"
          :to-ref="vueRef"
          :curvature="50"
          :start-y-offset="10"
          :end-y-offset="0"
        />
        <AnimatedBeam
          :container-ref="containerRef"
          :from-ref="inertiaRef"
          :to-ref="reactRef"
          :curvature="0"
          :start-y-offset="0"
          :end-y-offset="0"
        />
        <AnimatedBeam
          :container-ref="containerRef"
          :from-ref="inertiaRef"
          :to-ref="svelteRef"
          :curvature="-50"
          :start-y-offset="-10"
          :end-y-offset="0"
        />
        <div ref="wordpress" class="wordpress-logo-container">
          <img
            class="wordpress-logo logo-dark"
            src="/wordpress-logo-dark.png"
          />
          <img
            class="wordpress-logo logo-light"
            src="/wordpress-logo-light.png"
          />
        </div>
        <div ref="inertia" class="inertia-logo-container">
          <InertiaLogo />
        </div>
        <div class="frameworks-container">
          <a
            class="framework-icon"
            ref="vue"
            href="https://vuejs.org/"
            target="_blank"
            title="Vue"
            style="text-decoration: none"
          >
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/9/95/Vue.js_Logo_2.svg"
              style="height: 50px"
            />
          </a>
          <a
            class="framework-icon react-icon"
            ref="react"
            href="https://react.dev/"
            target="_blank"
            title="React"
            style="text-decoration: none"
          >
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg"
              style="height: 50px"
            />
          </a>
          <a
            class="framework-icon"
            ref="svelte"
            href="https://svelte.dev/"
            target="_blank"
            title="Svelte"
            style="text-decoration: none"
          >
            <img
              src="https://raw.githubusercontent.com/sveltejs/branding/refs/heads/master/svelte-logo.svg"
              style="height: 50px"
            />
          </a>
        </div>
      </div>
    </template>
  </Layout>
</template>

<script setup>
import { useTemplateRef } from "vue";
import DefaultTheme from "vitepress/theme";
import AnimatedBeam from "../components/AnimatedBeam.vue";
import InertiaLogo from "../components/InertiaLogo.vue";

const { Layout } = DefaultTheme;

const containerRef = useTemplateRef("container");
const wordpressRef = useTemplateRef("wordpress");
const inertiaRef = useTemplateRef("inertia");
const vueRef = useTemplateRef("vue");
const reactRef = useTemplateRef("react");
const svelteRef = useTemplateRef("svelte");
</script>

<style lang="postcss">
html {
  .logo-light {
    display: block;
  }
  .logo-dark {
    display: none;
  }

  &.dark {
    .logo-dark {
      display: block;
    }
    .logo-light {
      display: none;
    }
  }
}

.wordpress-logo {
  position: relative;
  height: 75px;
  width: auto;
  object-fit: contain;
}
.wordpress-logo-container,
.inertia-logo-container {
  z-index: 10;
  background-color: var(--vp-c-bg);
  padding: 0.25rem;
}
.framework-icon {
  z-index: 10;
}
.react-icon {
  background-color: var(--vp-c-bg);
}
.inertia-logo-container {
  display: flex;
  align-items: center;
  padding-bottom: 1rem;
}

.front-beam-container {
  max-width: 1152px;
  padding: 24px 24px;
  margin: 0 auto;
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: calc(
    (var(--vp-nav-height) + var(--vp-layout-top-height, 0px)) * -1
  );
}

@media (min-width: 640px) {
  .front-beam-container {
    padding-left: 48px;
    padding-right: 48px;
    padding-bottom: calc(
      (var(--vp-nav-height) + var(--vp-layout-top-height, 0px))
    );
  }
}

@media (min-width: 960px) {
  .front-beam-container {
    padding-left: 64px;
    padding-right: 64px;
    padding-bottom: calc(
      (var(--vp-nav-height) + var(--vp-layout-top-height, 0px))
    );
  }
}

@media (min-width: 1176px) {
  .front-beam-container {
    padding-left: 0;
    padding-right: 0;
  }
}

.frameworks-container {
  display: flex;
  flex-direction: column;
  row-gap: 1rem;
  align-items: center;
}
</style>
