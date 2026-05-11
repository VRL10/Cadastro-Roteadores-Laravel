<script setup lang="ts">
import AppConteudo from '@/components/AppConteudo.vue';
import AppShell from '@/components/AppShell.vue';
import AppBarraLateral from '@/components/AppBarraLateral.vue';
import AppCabecalhoBarraLateral from '@/components/AppCabecalhoBarraLateral.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppBarraLateral />
        <AppConteudo variant="sidebar">
            <AppCabecalhoBarraLateral :breadcrumbs="breadcrumbs" />
            <slot />
        </AppConteudo>
    </AppShell>
</template>
