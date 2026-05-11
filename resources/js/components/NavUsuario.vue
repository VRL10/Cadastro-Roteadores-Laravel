<script setup lang="ts">
import InfoUsuario from '@/components/InfoUsuario.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import ConteudoMenuUsuario from './ConteudoMenuUsuario.vue';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
</script>

<template>
    <!-- SidebarMenu renderiza o menu -->
    <SidebarMenu>
        <SidebarMenuItem>
            <!-- DropdownMenu (de ui/dropdown-menu) gerencia o abrir/fechar do menu -->
            <DropdownMenu>
                <!-- DropdownMenuTrigger é o botão que abre o menu quando clica -->
                <!-- as-child permite usar SidebarMenuButton ao invés de um botão normal -->
                <DropdownMenuTrigger as-child>
                    <!-- SidebarMenuButton é o botão visual que mostra as info do usuário -->
                    <!-- data-[state=open]:... muda a cor quando está aberto -->
                    <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <!-- UserInfo é nosso componente que mostra nome e email -->
                        <UserInfo :user="user" />
                        <!-- Ícone que mostra setas para cima/baixo (feedback de que é clicável) -->
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                
                <!-- DropdownMenuContent é o menu que aparece quando clica -->
                <!-- side="bottom" coloca embaixo do botão -->
                <!-- align="end" alinha à direita -->
                <DropdownMenuContent class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg" side="bottom" align="end" :side-offset="4">
                    <!-- UserMenuContent é nosso componente com as opções (logout, configurações, etc) -->
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
