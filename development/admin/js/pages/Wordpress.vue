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
	<EvoSetting title="Remove Global Styles">
		<template #description>
			<p class="mb-2">
				Removes generated global styles and theme variables. Bear in mind that this can break Gutenberg blocks
				that depend upon them.
			</p>
		</template>
		<VSwitch v-model="settings.remove_global_styles" label="Remove Global Styles" />
	</EvoSetting>
	<EvoSetting title="Load Blocks Separately">
		<template #description>
			<p class="mb-2">Only load block styles when they are used in the page.</p>
			<p>
				This is
				<strong>not recommended currently</strong> if you use any Gutenberg blocks since dynamic loading of
				blocks styles/scripts has not yet been implemented.
			</p>
		</template>
		<VSwitch v-model="settings.load_blocks_separately" label="Load Blocks Separately" />
	</EvoSetting>
	<EvoSetting title="Block Roles From Admin Area">
		<template #description>
			<p class="mb-2">
				By default, anyone with a registered account (regardless of role) can access the Wordpress admin area.
				This may not be ideal if you only want to allow certain roles to comment.
			</p>
			<p class="mb-2">
				From here, you can select which roles should be <strong>blocked</strong> from accessing the admin area.
			</p>
		</template>
		<VSelect
			v-model="settings.blocked_admin_roles"
			label="Blocked Roles"
			:items="roles"
			:item-props="true"
			multiple
			chips
			hide-details
		/>
		<VCheckbox v-model="settings.blocked_admin_roles_hide_bar" label="Also hide admin bar" hide-details />
	</EvoSetting>
</template>

<script setup>
import { useSettings } from "composables/useSettings";
import EvoSetting from "components/Setting.vue";

const { settings } = useSettings([
	"remove_emojis",
	"remove_jquery",
	"remove_global_styles",
	"load_blocks_separately",
	"blocked_admin_roles",
	"blocked_admin_roles_hide_bar",
]);

const api = useApi();
const roles = ref([]);

api.get("roles").then((res) => {
	roles.value = res.data.roles;
});
</script>
