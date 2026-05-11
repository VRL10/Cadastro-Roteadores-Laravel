<script setup lang="ts">
// Importa hook do Inertia.js para gerenciar formulários
// useForm: gerencia submissão de formulário e erros
import { useForm } from '@inertiajs/vue3';

// Importa ref do Vue para referencías a elementos HTML
import { ref } from 'vue';

// ===== COMPONENTES IMPORTADOS =====
// Nossos próprios componentes
import TituloPequeno from '@/components/TituloPequeno.vue';
import ErroEntrada from '@/components/ErroEntrada.vue';

// Componentes de ui/ (design system)
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// ===== DADOS E LÓGICA DO COMPONENTE =====
// Referência ao input de senha (para focar quando tiver erro)
const entradaSenha = ref<HTMLInputElement | null>(null);

// Gerenciador de formulário do Inertia.js
// Controla o envio, validação e erros
const formulario = useForm({
    password: '',
});

// Função chamada quando o usuário clica "Deletar conta"
// Faz uma requisição DELETE ao servidor para deletar a conta
const excluirUsuario = (e: Event) => {
    // Previne comportamento padrão do formulário
    e.preventDefault();

    // Envia requisição DELETE para a rota profile.destroy
    // preserveScroll: mantém a posição do scroll
    // onSuccess: callback se conseguir deletar (fecha a modal)
    // onError: callback se tiver erro (foca no input de senha)
    // onFinish: callback sempre (limpa o formulário)
    formulario.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => entradaSenha.value?.focus(),
        onFinish: () => formulario.reset(),
    });
};

// Função para fechar a modal
// Limpa erros e reseta o formulário
const closeModal = () => {
    formulario.clearErrors();
    formulario.reset();
};
</script>

<template>
    <div class="space-y-6">
        <TituloPequeno title="Delete account" description="Delete your account and all of its resources" />
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">Please proceed with caution, this cannot be undone.</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive">Delete account</Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-6" @submit="excluirUsuario">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
                            <DialogDescription>
                                Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your
                                password to confirm you would like to permanently delete your account.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">Password</Label>
                            <Input id="password" type="password" name="password" ref="entradaSenha" v-model="formulario.password" placeholder="Password" />
                            <ErroEntrada :message="formulario.errors.password" />
                        </div>

                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="secondary" @click="closeModal"> Cancel </Button>
                            </DialogClose>

                            <Button variant="destructive" :disabled="formulario.processing">
                                <button type="submit">Delete account</button>
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
