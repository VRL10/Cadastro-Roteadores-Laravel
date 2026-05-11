<script setup lang="ts">
import ErroEntrada from '@/components/ErroEntrada.vue';
import LinkTexto from '@/components/LinkTexto.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LayoutAutenticacao from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const propriedades = defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const situacao = computed(() => propriedades.status);

const formulario = useForm({
    email: '',
    password: '',
    remember: false,
});

const enviarFormulario = () => {
    formulario.post(route('login'), {
        onFinish: () => formulario.reset('password'),
    });
};
</script>

<template>
    <LayoutAutenticacao title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="situacao" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ situacao }}
        </div>

        <form @submit.prevent="enviarFormulario" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="email"
                        v-model="formulario.email"
                        placeholder="email@example.com"
                    />
                    <ErroEntrada :message="formulario.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <LinkTexto v-if="canResetPassword" :href="route('password.request')" class="text-sm" tabindex="5"> Forgot password? </LinkTexto>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="2"
                        autocomplete="current-password"
                        v-model="formulario.password"
                        placeholder="Password"
                    />
                    <ErroEntrada :message="formulario.errors.password" />
                </div>

                <div class="flex items-center justify-between" tabindex="3">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="formulario.remember" tabindex="4" />
                        <span>Remember me</span>
                    </Label>
                </div>

                    <Button type="submit" class="mt-4 w-full" tabindex="4" :disabled="formulario.processing">
                        <LoaderCircle v-if="formulario.processing" class="h-4 w-4 animate-spin" />
                    Log in
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <LinkTexto :href="route('register')" :tabindex="5">Sign up</LinkTexto>
            </div>
        </form>
    </LayoutAutenticacao>
</template>
