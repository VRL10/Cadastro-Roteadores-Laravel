import { onMounted, ref } from 'vue';

type Aparencia = 'light' | 'dark' | 'system';

export function atualizarTema(valor: Aparencia) {
    if (valor === 'system') {
        const temaDoSistema = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', temaDoSistema === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', valor === 'dark');
    }
}

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const lidarComMudancaTemaSistema = () => {
    const aparenciaAtual = localStorage.getItem('appearance') as Aparencia | null;
    atualizarTema(aparenciaAtual || 'system');
};

export function inicializarTema() {
    const aparenciaSalva = localStorage.getItem('appearance') as Aparencia | null;
    atualizarTema(aparenciaSalva || 'system');

    mediaQuery.addEventListener('change', lidarComMudancaTemaSistema);
}

export function useAparencia() {
    const appearance = ref<Aparencia>('system');

    onMounted(() => {
        inicializarTema();

        const aparenciaSalva = localStorage.getItem('appearance') as Aparencia | null;

        if (aparenciaSalva) {
            appearance.value = aparenciaSalva;
        }
    });

    function atualizarAparencia(valor: Aparencia) {
        appearance.value = valor;
        localStorage.setItem('appearance', valor);
        atualizarTema(valor);
    }

    return {
        appearance,
        atualizarAparencia,
    };
}