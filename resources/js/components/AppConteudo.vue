<script setup lang="ts">
// Importa SidebarInset da ui/sidebar
// SidebarInset é um container que ajusta o espaço para quando tem sidebar
import { SidebarInset } from '@/components/ui/sidebar';

// Importa computed do Vue para variável que muda automaticamente
import { computed } from 'vue';

// ===== PROPRIEDADES DO COMPONENTE =====
// Este componente recebe:
// - variant: tipo de layout ("header" ou "sidebar")
// - class: classes CSS customizadas
interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

// Aceita as propriedades
const props = defineProps<Props>();

// Cria uma variável reativa das classes CSS (para usar no template)
const className = computed(() => props.class);
</script>

<template>
    <!-- Se o tipo é "sidebar", usa SidebarInset (que sabe como se adaptar ao lado da sidebar) -->
    <SidebarInset v-if="props.variant === 'sidebar'" :class="className">
        <!-- slot é o espaço que o conteúdo da página preenche -->
        <slot />
    </SidebarInset>
    
    <!-- Se o tipo é "header" (ou nada), usa um main tag normal -->
    <!-- Com tamanho máximo e layout flex -->
    <main v-else class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl" :class="className">
        <!-- Aqui vai o conteúdo da página -->
        <slot />
    </main>
</template>
