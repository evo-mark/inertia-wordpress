<template>
	<VBanner :icon="mdiConnection" v-bind="bannerBind">
		<VBannerText>Modules</VBannerText>
	</VBanner>
	<div class="prose mt-8 ml-4">
		<p>
			Select the modules you wish to enable. If the required plugin is available, its extra functionality will be
			provided.
		</p>
	</div>
	<VTable>
		<thead>
			<tr>
				<th>Module</th>
				<th>Category</th>
				<th>Enabled</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<!-- ADVANCED CUSTOM FIELDS -->
			<EvoModule v-model="settings.modules" category="Post Meta" value="acf" route="modules.acf">
				<template #logo>
					<div class="flex items-center gap-4 my-2">
						<AcfLogo class="h-12" />
						<h2 class="font-bold text-2xl">Advanced Custom Fields</h2>
					</div>
				</template>
			</EvoModule>
			<!-- CONTACT FORM 7 -->
			<EvoModule v-model="settings.modules" category="Forms" value="cf7" route="modules.cf7">
				<template #logo>
					<div class="my-2">
						<VImg
							src="https://contactform7.com/wp-content/uploads/contact-form-7-logo@2x.png"
							max-width="275px"
						/>
					</div>
				</template>
			</EvoModule>
			<!-- NINJA FORMS -->
			<EvoModule
				v-model="settings.modules"
				category="Forms"
				value="ninja-forms"
				route="modules.ninja-forms"
				unreleased
			>
				<template #logo>
					<div class="flex my-2">
						<VImg :src="NinjaFormsLogo" max-width="275px" min-width="275px" />
					</div>
				</template>
			</EvoModule>
			<!-- THE SEO FRAMEWORK -->
			<EvoModule
				v-model="settings.modules"
				value="seo-framework"
				category="Search Engine Optimisation"
				route="modules.seo-framework"
			>
				<template #logo>
					<div class="flex items-center gap-4 my-2">
						<img src="https://ps.w.org/autodescription/assets/icon.svg?rev=3000376" width="50px" />
						<h2 class="font-bold text-2xl">The SEO Framework</h2>
					</div>
				</template>
			</EvoModule>
			<!-- WEBP EXPRESS -->
			<EvoModule v-model="settings.modules" value="webp-express" category="Images" route="modules.webp-express">
				<template #logo>
					<div class="flex items-center gap-4 my-2">
						<WebPExpressLogo class="h-12" />
						<h2 class="font-bold text-2xl">WebP Express</h2>
					</div>
				</template>
			</EvoModule>
			<!-- WOOCOMMERCE -->
			<EvoModule
				v-model="settings.modules"
				category="ECommerce"
				value="woocommerce"
				route="modules.woocommerce"
				unreleased
			>
				<template #logo>
					<WooCommerceLogo class="h-12 my-2" />
				</template>
			</EvoModule>
			<!-- EXTERNAL MODULES -->
			<EvoModule
				v-for="module in externalModules"
				:key="module.slug"
				v-model="settings.modules"
				:value="module.slug"
				category="External"
			>
				<template #logo>
					<VImg v-if="module.logo" :src="module.logo" max-width="275px" />
					<h3 v-else class="text-3xl font-bold">{{ module.title }}</h3>
				</template>
			</EvoModule>
		</tbody>
	</VTable>
</template>

<script setup>
import AcfLogo from "components/AcfLogo.vue";
import WooCommerceLogo from "components/WooCommerceLogo.vue";
import NinjaFormsLogo from "../../../images/ninja-forms-red.png?url";
import WebPExpressLogo from "components/WebPExpressLogo.vue";
import { mdiConnection } from "@mdi/js";
import { useSettings } from "composables/useSettings";
import EvoModule from "components/Module.vue";
import { useApi } from "composables/useApi";

const bannerBind = {
	class: "bg-teal-500 border-teal-600 text-white",
	theme: "dark",
	density: "compact",
};

const api = useApi();
const { settings } = useSettings(["modules"]);
const modules = ref([]);
const externalModules = computed(() => modules.value.filter((m) => m.isInternal === false));

api.get("modules").then((res) => {
	modules.value = res.data.modules;
});
</script>
