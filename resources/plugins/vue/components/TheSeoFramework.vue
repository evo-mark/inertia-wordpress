<template>
  <Head :title="seo.title">
    <link v-for="link in seo.links" v-bind="link" />
    <meta v-for="meta in seo.meta" v-bind="meta" />
    <component is="script" type="application/ld+json">
      {{ schema }}
    </component>
  </Head>
</template>

<script setup>
import { computed } from "vue";
import { Head, usePage } from "@inertiajs/vue3";

const seo = computed(() => usePage().props.seo);
const schema = computed(() => {
  return JSON.stringify({
    "@context": "https://schema.org",
    "@graph": seo.value.schema,
  });
});
</script>
