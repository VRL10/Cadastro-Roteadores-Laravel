<script setup lang="ts">
import ErroEntrada from '@/components/ErroEntrada.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LayoutAutenticacao from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const propriedades = defineProps<Props>();

const formulario = useForm({
    token: propriedades.token,
    email: propriedades.email,
    password: '',
    password_confirmation: '',
});

const enviarFormulario = () => {
    formulario.post(route('password.store'), {
        onFinish: () => {
            formulario.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <LayoutAutenticacao title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <form @submit.prevent="enviarFormulario">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="mt-1 block w-full" readonly />
                    <ErroEntrada :message="formulario.errors.email" class="mt-2" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        v-model="formulario.password"
                        class="mt-1 block w-full"
                        autofocus
                        placeholder="Password"
                    />
                    <ErroEntrada :message="formulario.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation"> Confirm Password </Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        class="mt-1 block w-full"
                        placeholder="Confirm password"
                    />
                    <ErroEntrada :message="formulario.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-4 w-full" :disabled="formulario.processing">
                    <LoaderCircle v-if="formulario.processing" class="h-4 w-4 animate-spin" />
                    Reset password
                </Button>
            </div>
        </form>
    </LayoutAutenticacao>
</template>
