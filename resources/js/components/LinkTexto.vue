<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    href: string;
    tabindex?: number;
    method?: string;
    as?: string;
}

defineProps<Props>();
</script>

<template>
    <!-- Link do Inertia.js (navega sem recarregar) -->
    <Link
        :href="href"
        :tabindex="tabindex"
        :method="method"
        :as="as"
        <!-- Classes CSS para estilo de link:
             - text-foreground: cor do texto
             - underline: linha embaixo
             - decoration-neutral-300: cor da linha em modo claro
             - underline-offset-4: espaço entre texto e linha
             - transition-colors: anima a cor quando muda
             - hover:decoration-current!: muda a cor da linha ao passar mouse (! força) -->
        class="hover:decoration-current! text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out dark:decoration-neutral-500"
    >
        <!-- slot: o texto do link que quem usar esse componente vai passar -->
        <!-- Ex: <TextLink href="/home">Clique aqui</TextLink> -->
        <slot />
    </Link>
</template>
