<template>
	<div class="prose">
		<h2 class="text-xl mt-0 mb-4">Documentation</h2>
		<p>Full instructions for using this module are available on our documentation website</p>
		<VBtn class="not-prose" color="info" :href="href" target="_blank" :prepend-icon="mdiHelpNetworkOutline">
			<slot></slot>
		</VBtn>
	</div>
</template>

<script setup>
import { mdiHelpNetworkOutline } from "@mdi/js";

const { href } = defineProps({
	href: {
		type: String,
		required: true,
	},
});
</script>
