<script setup lang="ts">
import AppConteudo from '@/components/AppConteudo.vue';
import AppCabecalho from '@/components/AppCabecalho.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppCabecalho :breadcrumbs="breadcrumbs" />
        <AppConteudo>
            <slot />
        </AppConteudo>
    </AppShell>
</template>
