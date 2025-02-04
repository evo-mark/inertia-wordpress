<template>
	<EvoSetting title="Remove Emojis">
		<template #description>
			<p class="mb-2">
				By default, Wordpress includes a lot of code in your frontend to enable the rendering and detection of
				emojis. This is usually not required for headless builds and can be removed.
			</p>
		</template>
		<VSwitch v-model="settings.remove_emojis" label="Remove Emojis" />
	</EvoSetting>
	<EvoSetting title="Remove jQuery">
		<template #description>
			<p class="mb-2">
				Removing jQuery can lighten the load on headless sites. Be careful though, if you're using any plugins
				that render frontend output, chances are, they depend on jQuery.
			</p>
		</template>
		<VSwitch v-model="settings.remove_jquery" label="Remove jQuery" />
	</EvoSetting>
</template>

<script setup>
import { mdiServerNetwork, mdiTimer, mdiProjectorScreen } from "@mdi/js";
import { useSettings } from "composables/useSettings";
import EvoSetting from "components/Setting.vue";

const { settings } = useSettings(["remove_emojis", "remove_jquery"]);
</script>
