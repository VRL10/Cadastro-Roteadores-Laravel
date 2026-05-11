<script setup lang="ts">
import ErroEntrada from '@/components/ErroEntrada.vue';
import LinkTexto from '@/components/LinkTexto.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LayoutAutenticacao from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const propriedades = defineProps<{
    status?: string;
}>();

const situacao = computed(() => propriedades.status);

const formulario = useForm({
    email: '',
});

const enviarFormulario = () => {
    formulario.post(route('password.email'));
};
</script>

<template>
    <LayoutAutenticacao title="Forgot password" description="Enter your email to receive a password reset link">
        <Head title="Forgot password" />

        <div v-if="situacao" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ situacao }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="enviarFormulario">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="formulario.email" autofocus placeholder="email@example.com" />
                    <ErroEntrada :message="formulario.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="formulario.processing">
                        <LoaderCircle v-if="formulario.processing" class="h-4 w-4 animate-spin" />
                        Email password reset link
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Or, return to</span>
                <LinkTexto :href="route('login')">log in</LinkTexto>
            </div>
        </div>
    </LayoutAutenticacao>
</template>
