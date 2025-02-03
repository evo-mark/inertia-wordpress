<template>
	<router-link :to="{ name: 'modules' }">
		<VBtn variant="text" :prepend-icon="mdiChevronLeft" color="warning">Return to Modules</VBtn>
	</router-link>
</template>

<script setup>
import { mdiChevronLeft } from "@mdi/js";
</script>
