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
				</div>
			</VCol>
			<VCol>
				<div class="prose">
					<h2 class="text-xl mt-0 mb-4">Example</h2>
					<p class="mb-2">
						Since all of the SEO props are managed through your page props, you will likely only need to use
						the component in a single place. Your default layout is an excellent location for this.
					</p>
				</div>
				<v-card>
					<v-tabs v-model="exampleTab" class="bg-teal-500 text-white">
						<v-tab value="vue">Vue</v-tab>
						<v-tab value="react">React</v-tab>
						<v-tab value="svelte">Svelte</v-tab>
					</v-tabs>

					<v-card-text>
						<v-tabs-window v-model="exampleTab">
							<v-tabs-window-item value="vue">
								<Code :code="seoFrameworkExampleVue" language="vue" />
							</v-tabs-window-item>
							<v-tabs-window-item value="react">
								<Code :code="seoFrameworkExampleReact" language="jsx" />
							</v-tabs-window-item>

							<v-tabs-window-item value="svelte">
								<Code :code="seoFrameworkExampleSvelte" language="html" />
							</v-tabs-window-item>
						</v-tabs-window>
					</v-card-text>
				</v-card>
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
import { seoFrameworkExampleVue, seoFrameworkExampleReact, seoFrameworkExampleSvelte } from "../../code/seoFramework";
import Code from "components/Code.vue";

const { settings } = useSettings(["modules"]);
const isEnabled = computed(() => settings.value?.modules && settings.value.modules.includes("cf7"));

const exampleTab = ref("vue");
</script>
