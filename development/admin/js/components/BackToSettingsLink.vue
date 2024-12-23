<template>
	<router-link :to="{ name: 'settings' }">
		<VBtn variant="text" :prepend-icon="mdiChevronLeft" color="warning">Return to Settings</VBtn>
	</router-link>
</template>

<script setup>
import { mdiChevronLeft } from "@mdi/js";
</script>
