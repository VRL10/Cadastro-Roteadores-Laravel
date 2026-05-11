<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    migalhasNavegacao?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    migalhasNavegacao: () => [],
});
</script>

<template>
    <AppLayout :migalhasNavegacao="migalhasNavegacao">
        <slot />
    </AppLayout>
</template>
