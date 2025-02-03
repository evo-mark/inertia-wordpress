<template>
	<VBanner :icon="mdiServerNetwork" v-bind="bannerBind">
		<VBannerText class="font-bold">Server-Side Rendering (SSR)</VBannerText>
	</VBanner>
	<EvoSetting title="Enabled">
		<template #description>
			<p class="mb-2">Wordpress should attempt to use Server-Side-Rendering.</p>
			<p>
				You must have started the SSR process by running <code>wp inertia:start-ssr</code> for this to be
				available.
			</p>
		</template>
		<VSwitch v-model="settings.ssr_enabled" label="Enabled" />
	</EvoSetting>
	<EvoSetting
		title="URL"
		description="The endpoint for your Inertia SSR server. You shouldn't generally need to change this, but this field must match your server settings."
	>
		<VTextField v-model="settings.ssr_url" label="SSR URL" />
	</EvoSetting>
	<VBanner :icon="mdiTimer" v-bind="bannerBind">
		<VBannerText>History</VBannerText>
	</VBanner>
	<EvoSetting
		title="Encrypt"
		description="When you visit pages with Inertia, page props are saved to the browser's history. If you save sensitive details with page props, it's recommended to encrypt the data."
	>
		<VSwitch v-model="settings.history_encrypt" label="Encrypt" />
	</EvoSetting>
	<VBanner :icon="mdiProjectorScreen" v-bind="bannerBind">
		<VBannerText>Theme Settings</VBannerText>
	</VBanner>
	<EvoSetting
		title="Root Template"
		description="The location of your root PHP template in your theme. This is the file that calls `inertia_head()` and `inertia_body()`."
	>
		<VTextField v-model="settings.root_template" label="Root Template" />
	</EvoSetting>
	<EvoSetting
		title="Theme Namespace"
		description="The name vite uses for identifying your theme's files. Must match the one given in `vite.config.js`."
	>
		<VTextField v-model="settings.entry_namespace" label="Theme Namespace" />
	</EvoSetting>
	<EvoSetting
		title="Theme Entry File"
		description="The entry file for your theme. Must match the one given in `vite.config.js`."
	>
		<VTextField v-model="settings.entry_file" label="Theme Entry File" />
	</EvoSetting>
	<EvoSetting
		title="Templates Folder"
		description="Choose a location for files that should be made available as page/post templates in Wordpress. When selected, a template will be automatically loaded by the `resolveInertiaPage` helper"
	>
		<VTextField v-model="settings.templates_directory" label="Templates Folder" />
	</EvoSetting>
</template>

<script setup>
import { mdiServerNetwork, mdiTimer, mdiProjectorScreen } from "@mdi/js";
import { useSettings } from "composables/useSettings";
import EvoSetting from "components/Setting.vue";

const { settings } = useSettings([
	"ssr_enabled",
	"ssr_url",
	"history_encrypt",
	"root_template",
	"entry_namespace",
	"entry_file",
	"templates_directory",
]);

const bannerBind = {
	class: "bg-teal-500 border-teal-600 text-white",
	theme: "dark",
	density: "compact",
};
</script>
