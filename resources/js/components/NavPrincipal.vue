<script setup lang="ts">
// Importa componentes de menu da ui/sidebar
// SidebarGroup: agrupa itens do menu
// SidebarGroupLabel: rótulo do grupo
// SidebarMenu, SidebarMenuButton, SidebarMenuItem: estrutura e botões do menu
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

// Importa tipo de dados compartilhados (vem do servidor)
import { type SharedData } from '@/types';

// Importa Link do Inertia.js para navegação sem recarregar
import { Link, usePage } from '@inertiajs/vue3';

// Importa tipo para ícones (é qualquer componente Vue)
import type { Component } from 'vue';

// ===== DEFINIÇÃO DE TIPO DO ITEM DO MENU =====
// Cada item do menu tem:
// - title: nome que aparece (ex: "Dashboard")
// - url: para onde leva quando clica
// - icon: qual ícone mostrar
interface NavItem {
    title: string;
    url: string;
    icon: Component;
}

// ===== PROPRIEDADES DO COMPONENTE =====
// Este componente recebe uma lista de itens (vem de AppBarraLateral como itensNavegacaoPrincipal)
defineProps<{
    itens: NavItem[];
}>();

// Pega a página atual para saber qual menu item está ativo
const pagina = usePage<SharedData>();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in itens" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.url === pagina.url">
                    <Link :href="item.url">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
