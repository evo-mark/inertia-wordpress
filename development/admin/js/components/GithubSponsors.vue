<template>
	<VBtn
		v-tooltip:left="`Help keep this project going, support open-source.`"
		color="info"
		variant="elevated"
		href="https://github.com/sponsors/craigrileyuk/button"
	>
		<template #prepend>
			<VIcon :icon="mdiHeartOutline" class="text-pink-300" />
		</template>
		Sponsor on Github
	</VBtn>
</template>

<script setup>
import { mdiHeartOutline } from "@mdi/js";
</script>
