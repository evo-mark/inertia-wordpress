<template>
	<header>
		<VRow>
			<VCol>
				<div class="flex items-center gap-4">
					<AcfLogo class="h-12" />
					<h2 class="font-bold">Advanced Custom Fields</h2>
				</div>
			</VCol>
			<VCol align="right">
				<ModuleExternalLink href="https://www.advancedcustomfields.com/resources/" />
			</VCol>
		</VRow>
	</header>
	<main>
		<VRow>
			<VCol>
				<ModuleStatus :enabled="isEnabled" />
				<div class="prose">
					<p>
						Advanced Custom Fields (ACF) is one of the most essential plugins in the Wordpress ecosystem.
						Allowing you to easily manage the creation and display of post meta, custom post types and even
						PHP-flavoured blocks.
					</p>
					<h2 class="text-xl mt-0">Module Features</h2>
					<ul>
						<li>
							Automatic sharing of any fields linked to the current post on
							<code>$page.props.acf.post</code>
						</li>
						<li>Fully-scoped sharing of global options pages on <code>$page.props.acf.options</code></li>
					</ul>
					<h2 class="text-xl mt-0">Settings</h2>
					<VEmptyState :icon="mdiCogOffOutline" text="There are no settings available for this module" />
				</div>
			</VCol>
			<VCol>
				<DocumentationLink href="https://inertia-wordpress.evomark.co.uk/modules/advanced-custom-fields">
					Advanced Custom Fields
				</DocumentationLink>
			</VCol>
		</VRow>
	</main>
	<footer class="mt-4">
		<BackToSettingsLink />
	</footer>
</template>

<script setup>
import AcfLogo from "components/AcfLogo.vue";
import ModuleStatus from "components/ModuleStatus.vue";
import ModuleExternalLink from "components/ModuleExternalLink.vue";
import BackToSettingsLink from "components/BackToSettingsLink.vue";
import DocumentationLink from "components/DocumentationLink.vue";
import { useSettings } from "composables/useSettings";
import { computed } from "vue";
import { mdiCogOffOutline } from "@mdi/js";

const { settings } = useSettings(["modules"]);
const isEnabled = computed(() => settings.value?.modules && settings.value.modules.includes("acf"));
</script>
