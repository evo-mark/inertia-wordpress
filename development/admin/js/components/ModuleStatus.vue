<template>
	<div class="mb-4">
		<VChip label :color="enabled ? 'success' : 'error'">Status: {{ enabled ? "Enabled" : "Disabled" }}</VChip>
	</div>
</template>

<script setup>
const { enabled } = defineProps({
	enabled: {
		type: Boolean,
		default: false,
	},
});
</script>
