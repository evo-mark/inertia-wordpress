<template>
	<header>
		<VRow>
			<VCol>
				<WooCommerceLogo class="h-20" />
			</VCol>
			<VCol align="right">
				<ModuleExternalLink href="https://woocommerce.com/documentation/woocommerce/" />
			</VCol>
		</VRow>
	</header>
	<main>
		<VRow>
			<VCol>
				<ModuleStatus :enabled="isEnabled" />
				<div class="prose">
					<p>
						The official eCommerce extension for Wordpress brings you the power of a full-suite of store
						management functionality, helping merchants and developers build successful businesses for the
						long term.
					</p>
					<h2 class="text-xl mt-0">Module Features</h2>
					<ul>
						<li>Initialises the WooCommerce store for your frontend</li>
						<li>Automatically updated mini-cart shared to props</li>
						<li>Store options shared to props</li>
						<li>Inertia-optimised REST API endpoints for common functions</li>
					</ul>
				</div>
			</VCol>
			<VCol>
				<div class="prose">
					<h2 class="text-xl mt-0">REST API Endpoints</h2>
					<VAlert density="compact" color="info">
						All Inertia Wordpress endpoints begin with:<br />
						<code>usePage().props.wp.restUrl + "inertia-wordpress/v1/</code>
					</VAlert>
					<VList>
						<RestEndpoint
							description="Add an item to the user's cart"
							:args="[
								{
									name: 'productId',
									type: 'int|string',
									description: '',
								},
								{
									name: 'quantity',
									type: 'int',
									description: 'The quantity to add',
								},
							]"
						>
							<template #method>
								<VChip class="mr-2 bg-blue-500 text-white" label>POST</VChip>
							</template>

							<span class="ml-1">modules/woocommerce/cart</span>
						</RestEndpoint>
						<RestEndpoint
							description="Update the total of a product in a user's cart"
							:args="[
								{
									name: 'productId',
									type: 'int|string',
									description: '',
								},
								{
									name: 'quantity',
									type: 'int',
									description: 'The new quantity of the product',
								},
							]"
						>
							<template #method>
								<VChip class="mr-2 bg-teal-500 text-white" label>PATCH</VChip>
							</template>
							<span class="ml-1">modules/woocommerce/cart</span>
						</RestEndpoint>
						<RestEndpoint description="Remove an item from a user's cart">
							<template #method>
								<VChip class="mr-2 bg-red-500 text-white" label>DELETE</VChip>
							</template>
							<span class="ml-1">modules/woocommerce/cart/{productId}</span>
						</RestEndpoint>
					</VList>
				</div>
			</VCol>
		</VRow>
	</main>
	<footer class="mt-4">
		<BackToSettingsLink />
	</footer>
</template>

<script setup>
import WooCommerceLogo from "components/WooCommerceLogo.vue";
import ModuleStatus from "components/ModuleStatus.vue";
import ModuleExternalLink from "components/ModuleExternalLink.vue";
import BackToSettingsLink from "components/BackToSettingsLink.vue";
import RestEndpoint from "components/RestEndpoint.vue";
import { useSettings } from "composables/useSettings";
import { computed } from "vue";

const { settings } = useSettings(["modules"]);
const isEnabled = computed(() => settings.value?.modules && settings.value.modules.includes("woocommerce"));
</script>
