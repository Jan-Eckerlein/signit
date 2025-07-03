<script setup lang="ts">
import { client } from "@/api/client";
import { useQuery } from "@tanstack/vue-query";

const { data, isLoading, error, isError } = useQuery({
    queryKey: ["test-me"],
    queryFn: () =>
        client
            .GET("/api/test-me")
            .then((res) => res.data)
            .then((data) => data?.message),
});
</script>

<template>
    <div>
        <h2>HOME</h2>
        <router-link to="/test"> Take me to Test page </router-link>

        <div v-if="isLoading">Loading…</div>
        <div v-else-if="isError">❌ {{ error }}</div>
        <pre v-else>{{ data }}</pre>
    </div>
</template>
