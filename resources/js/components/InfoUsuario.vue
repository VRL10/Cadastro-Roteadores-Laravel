<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useIniciais } from '@/composables/useIniciais';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getIniciais } = useIniciais();
const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="user.avatar" :alt="user.name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getIniciais(user.name) }}
        </AvatarFallback>
    </Avatar>
    <div class="flex flex-1 flex-col truncate">
        <p class="truncate text-sm font-medium leading-none text-foreground">
            {{ user.name }}
        </p>
        <p v-if="showEmail" class="truncate text-xs leading-snug text-muted-foreground">
            {{ user.email }}
        </p>
    </div>
</template>
    </Avatar>

    <!-- Container com as informações de texto (nome e email) -->
    <div class="grid flex-1 text-left text-sm leading-tight">
        <!-- Nome do usuário em fonte mais pesada -->
        <!-- truncate evita que quebre para mais de uma linha -->
        <span class="truncate font-medium">{{ user.name }}</span>
        
        <!-- Email do usuário em texto menor e cor mais clara -->
        <!-- v-if="showEmail" mostra só se foi passado showEmail=true -->
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
    </div>
</template>
