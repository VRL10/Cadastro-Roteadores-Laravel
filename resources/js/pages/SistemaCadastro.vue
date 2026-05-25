<script setup lang="ts">
<<<<<<< HEAD
=======
// Aqui fazemos com que o titulo seja alterado como um componente 
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
>>>>>>> 70ebf23 (Tela de login atualizada, rotas ajustadas.)
import { Head } from '@inertiajs/vue3';
import { reactive } from 'vue';

<<<<<<< HEAD
import SistemaCadastroConteudo from '@/components/SistemaCadastroConteudo.vue';
=======
// Chamando o arquivo ''
import SistemaCadastroConteudo from '@/Components/SistemaCadastroConteudo.vue';
>>>>>>> 70ebf23 (Tela de login atualizada, rotas ajustadas.)
import { useSistemaCadastro } from '@/composables/useSistemaCadastro';

const sistema = reactive(useSistemaCadastro());
</script>

<template>
    <Head title="Sistema de Cadastro" />

    <SistemaCadastroConteudo :sistema="sistema" />
</template>