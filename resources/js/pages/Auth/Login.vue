<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    // Fallback para submissão tradicional: garante envio do CSRF no body
    // e evita problemas intermitentes com XHR/Inertia ao autenticar.
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = tokenMeta ? tokenMeta.getAttribute('content') : null;

    const el = document.createElement('form');
    el.method = 'POST';
    el.action = route('login');

    const inputToken = document.createElement('input');
    inputToken.type = 'hidden';
    inputToken.name = '_token';
    inputToken.value = csrf || '';
    el.appendChild(inputToken);

    const inputEmail = document.createElement('input');
    inputEmail.type = 'hidden';
    inputEmail.name = 'email';
    inputEmail.value = form.email;
    el.appendChild(inputEmail);

    const inputPassword = document.createElement('input');
    inputPassword.type = 'hidden';
    inputPassword.name = 'password';
    inputPassword.value = form.password;
    el.appendChild(inputPassword);

    const inputRemember = document.createElement('input');
    inputRemember.type = 'hidden';
    inputRemember.name = 'remember';
    inputRemember.value = form.remember ? '1' : '0';
    el.appendChild(inputRemember);

    document.body.appendChild(el);
    el.submit();
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Senha" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Lembrar-me</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Esqueceu sua senha?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
>
                    Entrar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
