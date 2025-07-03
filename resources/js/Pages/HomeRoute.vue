<script setup lang="ts">
import { ref, onMounted } from "vue";
import { client } from "@/api/client";

const response = ref<string | undefined>(undefined);
const loading = ref(true);
const error = ref<unknown>(undefined);

onMounted(async () => {
    try {
        const res = await client.GET("/api/test-me");
        response.value = res.data;
    } catch (err) {
        error.value = err;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <h2>HOME</h2>
        <router-link to="/test"> Take me to Test page </router-link>

        <div v-if="loading">Loading…</div>
        <div v-else-if="error">❌ {{ error }}</div>
        <pre v-else>{{ response }}</pre>
    </div>
</template>
