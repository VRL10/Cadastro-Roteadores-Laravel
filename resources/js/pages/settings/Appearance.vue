<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AbasAparencia from '@/components/AbasAparencia.vue';
import TituloPequeno from '@/components/TituloPequeno.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const migalhasNavegacao: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :migalhasNavegacao="migalhasNavegacao">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <TituloPequeno title="Appearance settings" description="Update your account's appearance settings" />
                <AbasAparencia />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
