<script setup lang="ts">
import ErroEntrada from '@/components/ErroEntrada.vue';
import LinkTexto from '@/components/LinkTexto.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LayoutAutenticacao from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const formulario = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const enviarFormulario = () => {
    formulario.post(route('register'), {
        onFinish: () => formulario.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <LayoutAutenticacao title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="enviarFormulario" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" required autofocus tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <ErroEntrada :message="formulario.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <ErroEntrada :message="formulario.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <ErroEntrada :message="formulario.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <ErroEntrada :message="formulario.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="formulario.processing">
                    <LoaderCircle v-if="formulario.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <LinkTexto :href="route('login')" class="underline underline-offset-4" tabindex="6">Log in</LinkTexto>
            </div>
        </form>
    </LayoutAutenticacao>
</template>
