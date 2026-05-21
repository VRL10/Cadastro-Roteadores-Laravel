import '../css/app.css';

// Importações relacionadas ao Vue e Inertia.js
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Declaração de tipos para as variáveis de ambiente e a função glob, que são usadas no código para configurar o nome do aplicativo e resolver os componentes de página.
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

// Configuração do nome do app para o título das páginas. Ele é definido a partir da variável de ambiente VITE_APP_NAME, porém estou usando 'Laravel' como valor padrão.
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Cria a aplicação Inertia.js, definindo o título dinâmico para cada página, o caminho das páginas usando a função resolvePageComponent, e configurando o tema claro/escuro ao carregar a página. O progresso da navegação é configurado para ter uma cor específica.
createInertiaApp({
    // titulo dinâmico para cada página
    title: (title) => `${title} - ${appName}`,
    // Define o caminho das páginas do Inertia.js, usando a função resolvePageComponent para resolver os componentes de página com base no nome da rota...
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
