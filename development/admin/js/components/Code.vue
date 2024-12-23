<template>
	<div class="relative">
		<pre
			class="whitespace-pre-wrap shadow-md my-1 relative"
		><code ref="codeRef" class="hljs rounded" :class="formattedLanguage" v-html="compiled"></code></pre>
		<v-btn
			v-if="copyIsSupported"
			variant="text"
			color="white"
			:icon="mdiClipboardOutline"
			class="absolute top-0 right-0"
			@click="copyCode"
		></v-btn>
	</div>
</template>

<script setup>
import { useSlots, computed } from "vue";
import { useClipboard } from "@vueuse/core";
import { mdiClipboardOutline } from "@mdi/js";
import hljs from "highlight.js/lib/core";
import hljsPhp from "highlight.js/lib/languages/php";
import hljsJavascript from "highlight.js/lib/languages/javascript";
import hljsTypescript from "highlight.js/lib/languages/typescript";
import hljsHtml from "highlight.js/lib/languages/xml";
import hljsJson from "highlight.js/lib/languages/json";
import hljsCss from "highlight.js/lib/languages/css";
import hljsVue from "../plugins/highlightjs-vue";

hljs.registerLanguage("html", hljsHtml);
hljs.registerLanguage("js", hljsJavascript);
hljs.registerLanguage("ts", hljsTypescript);
hljs.registerLanguage("php", hljsPhp);
hljs.registerLanguage("json", hljsJson);
hljs.registerLanguage("css", hljsCss);
hljs.registerLanguage("vue", hljsVue);

const props = defineProps({
	language: {
		type: String,
		default: "js",
	},
	lineNumbers: {
		type: Boolean,
		default: false,
	},
	code: {
		type: String,
		default: null,
	},
});

const codeRef = ref(null);
const formattedLanguage = computed(() => props.language.toLowerCase());
const getSlotContent = (slot) => {
	if (Array.isArray(slot) === false) return slot;
	return slot.reduce((acc, curr) => {
		if (Array.isArray(curr.children)) return acc + getSlotContent(curr.children);
		else return acc + curr.children;
	}, "");
};
const codeProxy = computed(() => {
	const raw = props.code ?? (slots.default ? slots.default() : "");
	return getSlotContent(raw);
});
const { copy, isSupported: copyIsSupported } = useClipboard({ source: codeProxy });

const slots = useSlots();
const compiled = computed(() => {
	const render = hljs.highlight(codeProxy.value, { language: formattedLanguage.value });
	return render.value;
});

const copyCode = () => {
	window.$evo.toast.send("Code was copied to clipboard");
	copy();
};
</script>

<style lang="postcss">
@import "highlight.js/styles/github-dark.min.css";
</style>
