<script setup lang="ts">
import NavRodape from '@/components/NavRodape.vue';
import NavPrincipal from '@/components/NavPrincipal.vue';
import NavUsuario from '@/components/NavUsuario.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogotipo from './AppLogotipo.vue';

const itensNavegacaoPrincipal: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const itensNavegacaoRodape: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogotipo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavPrincipal :itens="itensNavegacaoPrincipal" />
        </SidebarContent>

        <SidebarFooter>
            <NavRodape :items="itensNavegacaoRodape" />
            <NavUsuario />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
