<template>
	<VApp>
		<VAppBar absolute class="main-app-bar relative" color="transparent">
			<div class="absolute top-1/2 left-1/4 h-full w-full overflow-hidden -translate-y-1/2 flex items-center">
				<svg class="w-auto opacity-20 h-20" style="fill: rgb(178, 182, 255)" viewBox="0 0 95 52.8">
					<path d="M27.3 0H0l26.4 26.4L0 52.8h27.3l26.4-26.4z"></path>
					<path d="M68.6 0H41.3l26.4 26.4-26.4 26.4h27.3L95 26.4z"></path>
				</svg>
			</div>
			<InertiaLogo class="h-8 ml-8" />
			<VAppBarTitle>
				<span class="text-xs uppercase text-white">The Modern Monolith</span>
			</VAppBarTitle>
		</VAppBar>
		<VMain>
			<VContainer fluid>
				<router-view></router-view>
			</VContainer>
		</VMain>
	</VApp>
</template>

<script setup>
import InertiaLogo from "components/InertiaLogo.vue";
</script>

<style lang="postcss">
.main-app-bar {
	background-image: linear-gradient(to right, #9553e9, #6d74ed) !important;
}
</style>
