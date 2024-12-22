<template>
	<VBtn variant="text" :href="href" :prepend-icon="mdiOpenInNew" color="info" target="_blank">
		<slot>{{ title }}</slot>
	</VBtn>
</template>

<script setup>
import { mdiOpenInNew } from "@mdi/js";

const { title, href } = defineProps({
	title: {
		type: String,
		default: "Plugin Documentation",
	},
	href: {
		type: String,
		default: "#",
	},
});
</script>
