<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useIniciais } from '@/composables/useIniciais';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const propriedades = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getIniciais } = useIniciais();
const mostrarAvatar = computed(() => propriedades.user.avatar && propriedades.user.avatar !== '');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="mostrarAvatar" :src="propriedades.user.avatar" :alt="propriedades.user.name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getIniciais(propriedades.user.name) }}
        </AvatarFallback>
    </Avatar>
    <div class="flex flex-1 flex-col truncate">
        <p class="truncate text-sm font-medium leading-none text-foreground">
            {{ propriedades.user.name }}
        </p>
        <p v-if="propriedades.showEmail" class="truncate text-xs leading-snug text-muted-foreground">
            {{ propriedades.user.email }}
        </p>
    </div>
</template>
