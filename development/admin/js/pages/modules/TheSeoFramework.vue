<template>
	<header>
		<VRow>
			<VCol>
				<div class="flex items-center gap-4">
					<img src="https://ps.w.org/autodescription/assets/icon.svg?rev=3000376" width="50px" />
					<h2 class="font-bold">The SEO Framework</h2>
				</div>
			</VCol>
			<VCol align="right">
				<ModuleExternalLink href="https://theseoframework.com/docs/" />
			</VCol>
		</VRow>
	</header>
	<main>
		<VRow>
			<VCol>
				<ModuleStatus :enabled="isEnabled" />
				<div class="prose">
					<p>
						The SEO Framework is an expert system for SEO. It is a solution that can intelligently generate
						critical SEO meta tags in any language by reading your WordPress environment. This automation
						saves you a considerable amount of time that could be used to write more content or focus on
						other tasks. It also removes the need for advanced SEO knowledge.
					</p>
					<h2 class="text-xl mt-0">Module Features</h2>
					<ul>
						<li>Compiles SEO links, meta tags and JSON schema</li>
						<li>Prevents the SEO tags from being directly written to the page</li>
						<li>Redirects the tags to <code>$page.props.seo</code></li>
						<li>Helper components for all frameworks</li>
					</ul>
					<h2 class="text-xl mt-0">Settings</h2>
					<VEmptyState :icon="mdiCogOffOutline" text="There are no settings available for this module" />
				</div>
			</VCol>
			<VCol>
				<DocumentationLink href="https://inertia-wordpress.evomark.co.uk/modules/the-seo-framework">
					The SEO Framework
				</DocumentationLink>
			</VCol>
		</VRow>
	</main>
	<footer class="mt-4">
		<BackToSettingsLink />
	</footer>
</template>

<script setup>
import { computed } from "vue";
import { useSettings } from "composables/useSettings";
import ModuleStatus from "components/ModuleStatus.vue";
import ModuleExternalLink from "components/ModuleExternalLink.vue";
import BackToSettingsLink from "components/BackToSettingsLink.vue";
import { mdiCogOffOutline } from "@mdi/js";
import DocumentationLink from "components/DocumentationLink.vue";

const { settings } = useSettings(["modules"]);
const isEnabled = computed(() => settings.value?.modules && settings.value.modules.includes("cf7"));
</script>
