<script setup lang="ts">
// Aqui fazemos com que o titulo seja alterado como um componente 
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive } from 'vue';

// Chamando o arquivo ''
import SistemaCadastroConteudo from '@/Components/SistemaCadastroConteudo.vue';
import { useSistemaCadastro } from '@/composables/useSistemaCadastro';

const sistema = reactive(useSistemaCadastro());
</script>

<template>
    <Head title="Sistema de Cadastro" />

    <SistemaCadastroConteudo :sistema="sistema" />
</template>