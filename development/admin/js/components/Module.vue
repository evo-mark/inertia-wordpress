<template>
	<tr>
		<td>
			<slot name="logo"></slot>
		</td>
		<td>
			<VChip label variant="outlined" class="text-teal-500">
				{{ props.category }}
			</VChip>
		</td>
		<td v-if="props.unreleased" colspan="2">
			<span class="italic">Coming Soon</span>
		</td>
		<template v-else>
			<td>
				<VSwitch v-model="modelValue" :value="props.value" hide-details />
			</td>
			<td>
				<router-link v-if="props.route" :to="{ name: props.route }">
					<VBtn
						v-tooltip="`Module information and settings`"
						:icon="mdiPageNextOutline"
						variant="text"
						color="info"
					/>
				</router-link>
			</td>
		</template>
	</tr>
</template>

<script setup>
import { mdiPageNextOutline } from "@mdi/js";

const props = defineProps({
	category: {
		type: String,
		default: "",
	},
	route: {
		type: String,
		default: null,
	},
	value: {
		type: String,
		required: true,
	},
	unreleased: {
		type: Boolean,
		default: false,
	},
});
const modelValue = defineModel({
	type: Array,
	default: null,
});
</script>
