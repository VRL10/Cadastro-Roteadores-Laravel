<script setup lang="ts">
import ErroEntrada from '@/components/ErroEntrada.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LayoutAutenticacao from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const formulario = useForm({
    password: '',
});

const enviarFormulario = () => {
    formulario.post(route('password.confirm'), {
        onFinish: () => {
            formulario.reset();
        },
    });
};
</script>

<template>
    <LayoutAutenticacao title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

        <form @submit.prevent="enviarFormulario">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="formulario.password"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <ErroEntrada :message="formulario.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button class="w-full" :disabled="formulario.processing">
                        <LoaderCircle v-if="formulario.processing" class="h-4 w-4 animate-spin" />
                        Confirm Password
                    </Button>
                </div>
            </div>
        </form>
    </LayoutAutenticacao>
</template>
