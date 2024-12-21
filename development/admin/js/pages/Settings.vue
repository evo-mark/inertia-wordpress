<template>
	<VBanner :icon="mdiServerNetwork" class="bg-orange-100 border-orange-400 text-orange-900" density="compact">
		<VBannerText>Server-Side Rendering (SSR)</VBannerText>
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
	<VBanner :icon="mdiTimer" class="bg-orange-100 border-orange-400 text-orange-900" density="compact">
		<VBannerText>History</VBannerText>
	</VBanner>
	<EvoSetting
		title="Encrypt"
		description="When you visit pages with Inertia, page props are saved to the browser's history. If you save sensitive details with page props, it's recommended to encrypt the data."
	>
		<VSwitch v-model="settings.history_encrypt" label="Encrypt" />
	</EvoSetting>
	<VBanner :icon="mdiProjectorScreen" class="bg-orange-100 border-orange-400 text-orange-900" density="compact">
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
	<VBanner :icon="mdiConnection" class="bg-orange-100 border-orange-400 text-orange-900" density="compact">
		<VBannerText>Modules</VBannerText>
	</VBanner>
	<EvoSetting
		title="Enabled Modules"
		description="Select the modules you wish to enable. If the required plugin is available, its extra functionality will be provided."
	>
		<VRow>
			<VCol>
				<div class="flex items-center gap-4">
					<AcfLogo class="h-12" />
					<h2 class="font-bold">Advanced Custom Fields</h2>
				</div>
			</VCol>
			<VCol>
				<VSwitch v-model="settings.modules" label="Enabled" value="acf" />
			</VCol>
		</VRow>
		<VRow>
			<VCol>
				<VImg src="https://contactform7.com/wp-content/uploads/contact-form-7-logo@2x.png" max-width="275px" />
			</VCol>
			<VCol>
				<VSwitch v-model="settings.modules" label="Enabled" value="cf7" />
			</VCol>
		</VRow>
	</EvoSetting>
</template>

<script setup>
import { mdiServerNetwork, mdiTimer, mdiProjectorScreen, mdiConnection } from "@mdi/js";
import { useSettings } from "composables/useSettings";
import EvoSetting from "components/Setting.vue";
import AcfLogo from "components/AcfLogo.vue";

const { settings } = useSettings([
	"ssr_enabled",
	"ssr_url",
	"history_encrypt",
	"root_template",
	"modules",
	"entry_namespace",
	"entry_file",
]);
</script>
