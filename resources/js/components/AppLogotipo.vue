<script setup lang="ts">
// Importa o ícone do logo (SVG que mostra o símbolo da app)
import AppIconeLogotipo from '@/components/AppIconeLogotipo.vue';

// ===== PROPRIEDADES DO COMPONENTE =====
// Este componente recebe:
// - class: classes CSS customizadas (opcional)
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <!-- Container flex que mantém o ícone e o texto lado a lado -->
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <!-- AppLogoIcon: o símbolo visual da aplicação (dentro de um quadrado azul) -->
        <AppIconeLogotipo class="size-5 fill-current text-white dark:text-black" />
    </div>
    
    <!-- Seção do texto -->
    <div class="ml-1 grid flex-1 text-left text-sm">
        <!-- Nome da aplicação ("Laravel Starter Kit") -->
        <!-- truncate evita que quebra a linha se fico muito longo -->
        <span class="mb-0.5 truncate font-semibold leading-none">Laravel Starter Kit</span>
    </div>
</template>
