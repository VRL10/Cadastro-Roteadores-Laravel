<script setup lang="ts">
import AppConteudo from '@/components/AppConteudo.vue';
import AppCabecalho from '@/components/AppCabecalho.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    migalhasNavegacao?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    migalhasNavegacao: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppCabecalho :migalhasNavegacao="migalhasNavegacao" />
        <AppConteudo>
            <slot />
        </AppConteudo>
    </AppShell>
</template>
