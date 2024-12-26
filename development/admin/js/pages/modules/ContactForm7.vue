<template>
	<VContainer>
		<header>
			<VRow>
				<VCol>
					<VImg src="https://contactform7.com/wp-content/uploads/contact-form-7-logo@2x.png" width="400px" />
				</VCol>
				<VCol align="right">
					<ModuleExternalLink href="https://contactform7.com/docs/" />
				</VCol>
			</VRow>
		</header>
		<main>
			<VRow>
				<VCol>
					<ModuleStatus :enabled="isEnabled" />
					<div class="prose">
						<p>
							Contact Form 7 is a simple, lightweight plugin for handling the creation, validation and
							processing of user-submitted content on your Wordpress application.
						</p>
						<h2 class="text-xl mt-0">Module Features</h2>
						<ul>
							<li>Provides all registered forms to your frontend</li>
							<li>Shares reCaptcha data when enabled</li>
							<li>Disables CF7 JavaScript and CSS stylesheets</li>
							<li>Automatically handles validation errors with support for the InertiaJS form helper</li>
							<li>Extension for Inertia form helper for super easy forms</li>
							<li>Messages will be flashed to the <code>$page.props.flash.cf7</code> namespace</li>
						</ul>
						<h2 class="text-xl mt-0">Settings</h2>
					</div>
					<EvoSetting
						title="Disable Default CSS/JavaScript"
						description="When this is on, CF7 won't enqueue the default CSS and JavaScript resources for forms. You should turn this off if your have CF7 forms that you're rendering as CMS content."
					>
						<VSwitch
							v-model="settings.module_cf7_disable_frontend_resources"
							label="Disable Default Resources"
						/>
					</EvoSetting>
				</VCol>
				<VCol>
					<DocumentationLink href="https://inertia-wordpress.evomark.co.uk/modules/contact-form-7">
						Contact Form 7
					</DocumentationLink>
				</VCol>
			</VRow>
		</main>
		<footer class="mt-4">
			<BackToSettingsLink />
		</footer>
	</VContainer>
</template>

<script setup>
import { useSettings } from "composables/useSettings";
import EvoSetting from "components/Setting.vue";
import ModuleStatus from "components/ModuleStatus.vue";
import ModuleExternalLink from "components/ModuleExternalLink.vue";
import BackToSettingsLink from "components/BackToSettingsLink.vue";
import DocumentationLink from "components/DocumentationLink.vue";
import { computed } from "vue";

const { settings } = useSettings(["modules", "module_cf7_disable_frontend_resources"]);
const isEnabled = computed(() => settings.value?.modules && settings.value.modules.includes("cf7"));
</script>
