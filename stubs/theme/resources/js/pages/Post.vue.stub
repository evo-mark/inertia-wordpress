<template>
    <Head :title="post.title" />
    <main class="flex-centre">
        <h1>{{ post.title }}</h1>
        <img
            v-if="post.featuredImage"
            id="featured-image"
            :src="post.featuredImage.sizes.full.url"
        />

        <div v-html="post.content"></div>
        <div>
            Written by {{ post.author.name }} on {{ publishedDate }}
        </div>
    </main>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    post: {
        type: Object,
        default: () => ({}),
    },
});

const publishedDate = computed(() => {
    const d = new Date(props.post.createdAt);
    return d.toLocaleString(undefined, {
        dateStyle: "long",
        timeStyle: "medium",
    });
});
</script>
