<template>
	<div class="evo-rest-endpoint grid px-4 py-2 grid-cols-[90px_1fr]">
		<div class="evo-rest-endpoint__description font-bold pb-2">
			<VIcon :icon="mdiAccessPoint" class="mr-2" />
			<slot name="description">
				{{ description }}
			</slot>
		</div>
		<div class="evo-rest-endpoint__method">
			<slot name="method"></slot>
		</div>
		<div class="evo-rest-endpoint__path font-mono">
			<slot></slot>
		</div>
		<div class="evo-rest-endpoint__args">
			<VTable v-if="args.length" class="not-prose bg-stone-100" density="compact">
				<thead>
					<tr class="bg-stone-200">
						<th class="!font-bold">Argument</th>
						<th class="!font-bold">Type</th>
						<th class="!font-bold">Description</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="arg in args">
						<td>{{ arg.name }}</td>
						<td>{{ arg.type }}</td>
						<td>{{ arg.description }}</td>
					</tr>
				</tbody>
			</VTable>
		</div>
	</div>
</template>

<script setup>
import { mdiAccessPoint } from "@mdi/js";
const { args, description } = defineProps({
	args: {
		type: Array,
		default: () => [],
	},
	description: {
		type: String,
		default: "",
	},
});
</script>

<style lang="postcss">
.evo-rest-endpoint {
	grid-template-areas:
		"description description"
		"method path"
		"method args";

	&__description {
		grid-area: description;
	}

	&__method {
		grid-area: method;
	}

	&__path {
		grid-area: path;
	}

	&__args {
		grid-area: args;
	}
}
</style>
