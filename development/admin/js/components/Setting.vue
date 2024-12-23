<template>
	<div class="evo-setting grid gap-x-4 px-4 py-2">
		<div class="evo-setting__title">
			<slot name="title">
				<h3 class="font-bold text-lg pb-2">{{ props.title }}</h3>
			</slot>
		</div>
		<div class="evo-setting__description opacity-70">
			<slot name="description">
				{{ props.description }}
			</slot>
		</div>
		<div class="evo-setting__content">
			<slot></slot>
		</div>
	</div>
</template>

<script setup>
const props = defineProps({
	title: {
		type: String,
		default: "",
	},
	description: {
		type: String,
		default: "",
	},
});
</script>

<style lang="postcss">
.evo-setting {
	grid-template-areas:
		"title title"
		"description content";

	grid-template-columns: 50ch 1fr;

	&__title {
		grid-area: title;
	}

	&__description {
		grid-area: description;
	}

	&__content {
		grid-area: content;
	}
}
</style>
