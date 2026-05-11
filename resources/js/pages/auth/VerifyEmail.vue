<script setup lang="ts">
import LinkTexto from '@/components/LinkTexto.vue';
import { Button } from '@/components/ui/button';
import LayoutAutenticacao from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const propriedades = defineProps<{
    status?: string;
}>();

const situacao = computed(() => propriedades.status);

const formulario = useForm({});

const enviarFormulario = () => {
    formulario.post(route('verification.send'));
};
</script>

<template>
    <LayoutAutenticacao title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <div v-if="situacao === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="enviarFormulario" class="space-y-6 text-center">
            <Button :disabled="formulario.processing" variant="secondary">
                <LoaderCircle v-if="formulario.processing" class="h-4 w-4 animate-spin" />
                Resend verification email
            </Button>

            <LinkTexto :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Log out </LinkTexto>
        </form>
    </LayoutAutenticacao>
</template>
