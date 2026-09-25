<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import DefaultLayout from "@/layouts/DefaultLayout.vue";

const route = useRoute();
const useLayout = computed(() => route.meta.layout === "default");
</script>

<template>
  <DefaultLayout v-if="useLayout">
    <router-view />
  </DefaultLayout>
  <router-view v-else />
</template>
