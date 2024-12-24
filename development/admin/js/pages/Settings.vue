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
	<VBanner :icon="mdiConnection" v-bind="bannerBind">
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
			<VCol cols="auto">
				<VSwitch v-model="settings.modules" label="Enabled" value="acf" />
			</VCol>
			<VCol>
				<router-link :to="{ name: 'modules.acf' }">
					<VBtn v-tooltip="`More about this module`" :icon="mdiPageNextOutline" color="info" />
				</router-link>
			</VCol>
		</VRow>
		<VRow>
			<VCol>
				<VImg src="https://contactform7.com/wp-content/uploads/contact-form-7-logo@2x.png" max-width="275px" />
			</VCol>
			<VCol cols="auto">
				<VSwitch v-model="settings.modules" label="Enabled" value="cf7" />
			</VCol>
			<VCol>
				<router-link :to="{ name: 'modules.cf7' }">
					<VBtn v-tooltip="`More about this module`" :icon="mdiPageNextOutline" color="info" />
				</router-link>
			</VCol>
		</VRow>
		<VRow>
			<VCol>
				<div class="flex items-center gap-4">
					<img src="https://ps.w.org/autodescription/assets/icon.svg?rev=3000376" width="50px" />
					<h2 class="font-bold">The SEO Framework</h2>
				</div>
			</VCol>
			<VCol cols="auto">
				<VSwitch v-model="settings.modules" label="Enabled" value="seo-framework" />
			</VCol>
			<VCol>
				<router-link :to="{ name: 'modules.seo-framework' }">
					<VBtn v-tooltip="`More about this module`" :icon="mdiPageNextOutline" color="info" />
				</router-link>
			</VCol>
		</VRow>
		<VRow>
			<VCol>
				<WooCommerceLogo class="h-12" />
			</VCol>
			<VCol cols="auto">
				<span class="italic">Coming Soon</span>
				<!-- <VSwitch v-model="settings.modules" label="Enabled" value="woocommerce" /> -->
			</VCol>
			<VCol>
				<!-- <router-link :to="{ name: 'modules.woocommerce' }">
					<VBtn v-tooltip="`More about this module`" :icon="mdiPageNextOutline" color="info" />
				</router-link> -->
			</VCol>
		</VRow>
		<VRow v-for="module in externalModules">
			<VCol>
				<VImg v-if="module.logo" :src="module.logo" max-width="275px" />
				<h3 v-else class="text-3xl font-bold">{{ module.title }}</h3>
			</VCol>
			<VCol cols="auto">
				<VSwitch v-model="settings.modules" label="Enabled" :value="module.slug" />
			</VCol>
			<VCol></VCol>
		</VRow>
	</EvoSetting>
</template>

<script setup>
import { mdiServerNetwork, mdiTimer, mdiProjectorScreen, mdiConnection, mdiPageNextOutline } from "@mdi/js";
import { useSettings } from "composables/useSettings";
import { useApi } from "composables/useApi";
import EvoSetting from "components/Setting.vue";
import AcfLogo from "components/AcfLogo.vue";
import WooCommerceLogo from "components/WooCommerceLogo.vue";

const api = useApi();
const { settings } = useSettings([
	"ssr_enabled",
	"ssr_url",
	"history_encrypt",
	"root_template",
	"modules",
	"entry_namespace",
	"entry_file",
	"templates_directory",
]);

const bannerBind = {
	class: "bg-teal-500 border-teal-600 text-white",
	theme: "dark",
	density: "compact",
};

const modules = ref([]);
const externalModules = computed(() => modules.value.filter((m) => m.isInternal === false));

api.get("modules").then((res) => {
	modules.value = res.data.modules;
});
</script>
