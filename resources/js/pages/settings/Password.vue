<script setup lang="ts">
import ErroEntrada from '@/components/ErroEntrada.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import TituloPequeno from '@/components/TituloPequeno.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

interface Props {
    nomeClasse?: string;
}

defineProps<Props>();

const migalhasNavegacao: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: '/settings/password',
    },
];

const entradaSenha = ref<HTMLInputElement>();
const entradaSenhaAtual = ref<HTMLInputElement>();

const formulario = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const atualizarSenha = () => {
    formulario.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => formulario.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                formulario.reset('password', 'password_confirmation');
                if (entradaSenha.value instanceof HTMLInputElement) {
                    entradaSenha.value.focus();
                }
            }

            if (errors.current_password) {
                formulario.reset('current_password');
                if (entradaSenhaAtual.value instanceof HTMLInputElement) {
                    entradaSenhaAtual.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <AppLayout :migalhasNavegacao="migalhasNavegacao">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <TituloPequeno title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <form @submit.prevent="atualizarSenha" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Current Password</Label>
                        <Input
                            id="current_password"
                            ref="entradaSenhaAtual"
                            v-model="formulario.current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <ErroEntrada :message="formulario.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            ref="entradaSenha"
                            v-model="formulario.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <ErroEntrada :message="formulario.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <ErroEntrada :message="formulario.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="formulario.processing">Save password</Button>

                        <TransitionRoot
                            :show="formulario.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Saved</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
