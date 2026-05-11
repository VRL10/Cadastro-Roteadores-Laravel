<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { reactive } from 'vue';
import SistemaCadastroConteudo from '@/components/SistemaCadastroConteudo.vue';
import { useSistemaCadastro } from '@/composables/useSistemaCadastro';

const sistema = reactive(useSistemaCadastro());
</script>

<template>
    <Head title="Sistema de Cadastro" />
    <SistemaCadastroConteudo :sistema="sistema" />
</template>
