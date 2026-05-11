<script setup lang="ts">
import AppLogotipo from '@/components/AppLogotipo.vue';
import AppIconeLogotipo from '@/components/AppIconeLogotipo.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
// Button: botão estilizado e reutilizável (vem de ui/button)
import { Button } from '@/components/ui/button';
// DropdownMenu: menu que aparece quando clica (vem de ui/dropdown-menu)
// Tem 3 partes: o gatilho (DropdownMenuTrigger), o conteúdo (DropdownMenuContent), e itens
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
// NavigationMenu: menu de navegação horizontal (vem de ui/navigation-menu)
// Tem vários componentes: Lista, Item, Link, e estilos
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
// Sheet: uma caixa deslizante que aparece quando clica (vem de ui/sheet)
// Usada aqui para o menu móvel em telefone
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
// Tooltip: balãozinho que aparece quando passa o mouse (vem de ui/tooltip)
// Tem 3 partes: Provedor (context), Gatilho (trigger), e Conteúdo
import Tooltip from '@/components/tooltip/Tooltip.vue';
import TooltipContent from '@/components/tooltip/TooltipContent.vue';
import TooltipProvider from '@/components/tooltip/TooltipProvider.vue';
import TooltipTrigger from '@/components/tooltip/TooltipTrigger.vue';

// Importa o menu de usuário que criamos (componente próprio)
import ConteudoMenuUsuario from '@/components/ConteudoMenuUsuario.vue';

// Importa a função que pega as iniciais do nome (ex: "João Silva" vira "JS")
// Vem de composables/useIniciais.ts
import { getIniciais } from '@/composables/useIniciais';

// Importa tipos TypeScript que definem a estrutura de dados
import type { BreadcrumbItem, NavItem } from '@/types';

// Importa funções do Inertia.js para navegação e pega dados da página
// Link faz a navegação sem recarregar a página
// usePage pega informações do servidor (como dados do usuário logado)
import { Link, usePage } from '@inertiajs/vue3';

// Importa ícones já prontos da biblioteca Lucide Icons
import { BookOpen, Folder, LayoutGrid, Menu, Search } from 'lucide-vue-next';

// Importa função do Vue para criar variáveis computadas (que mudam automaticamente)
import { computed } from 'vue';

// ===== DEFINIÇÃO DE PROPRIEDADES =====
// Este componente pode receber "migalhasNavegacao" (o caminho de navegação tipo: Dashboard > Usuários > João)
interface Props {
    migalhasNavegacao?: BreadcrumbItem[];
}

// Usa as propriedades e define um valor padrão (array vazio se não passar nada)
const propriedades = withDefaults(defineProps<Props>(), {
    migalhasNavegacao: () => [],
});

// ===== LÓGICA DO COMPONENTE =====
// Pega a página atual do Inertia.js
const pagina = usePage();

// Extrai os dados de autenticação (dados do usuário logado)
// Se vem do servidor Laravel, pega as informações que lá foram definidas
const autenticacao = computed(() => pagina.props.auth);

// Função que verifica se uma rota/URL é a atual
// Usa isso para saber qual menu item deve ficar destacado
const rotaAtual = (url: string) => {
    return pagina.url === url;
};

// Cria um estilo computado que muda conforme qual página o usuário está
// Se está na página do Dashboard, o botão Dashboard fica com cor diferente
// Isso dá feedback visual de "você está aqui"
const estilosItemAtivo = computed(() => (url: string) => (rotaAtual(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''));

// ===== DADOS DOS MENUS =====
// Lista de links principais do menu (na frente é só Dashboard, mas pode adicionar mais)
const itensNavegacaoPrincipal: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

// Lista de links que ficam à direita (para repositório e documentação)
const itensNavegacaoDireita: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <div>
        <!-- ===== BARRA DE TOPO ===== -->
        <!-- Cria a barra cinzenta com borda inferior que fica lá em cima da página -->
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                
                <!-- ===== MENU PARA CELULAR ===== -->
                <!-- Só aparece em telas pequenas (lg:hidden = esconde em telas grandes) -->
                <!-- Sheet é a caixa deslizante que vem da ui/sheet -->
                <div class="lg:hidden">
                    <Sheet>
                        <!-- SheetTrigger é o botão que abre o menu (um ícone de 3 linhas) -->
                        <SheetTrigger :as-child="true">
                            <!-- Button vem de ui/button - é o botão estilizado -->
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <!-- Menu é um ícone (vem de lucide-vue-next) -->
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        
                        <!-- SheetContent é o conteúdo que aparece quando abre -->
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            
                                <!-- Menu com os links do dashboard (vem de itensNavegacaoPrincipal lá em cima) -->
                            <div class="flex flex-col justify-between h-full space-y-4 py-6 flex-1">
                                <nav class="-mx-3 space-y-1">
                                    <!-- Link é do Inertia.js - faz navegação sem recarregar -->
                                    <!-- A classe "activeItemStyles" destaca qual página está aberta -->
                                    <Link
                                        v-for="item in itensNavegacaoPrincipal"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="estilosItemAtivo(item.href)"
                                    >
                                        <!-- component é do Vue - renderiza o ícone dinamicamente -->
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                
                                <!-- Links à direita (repositório e documentação) -->
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <!-- ===== LOGO =====-->
                <!-- Link para voltar ao dashboard quando clica na logo -->
                <Link :href="route('dashboard')" class="flex items-center gap-x-2">
                    <!-- AppLogo é nosso próprio componente (não é de ui/) -->
                    <!-- hidden h-6 xl:block = esconde em telas pequenas, mostra em telas grandes -->
                    <AppLogo class="hidden h-6 xl:block" />
                </Link>

                <!-- ===== MENU PARA COMPUTADOR ===== -->
                <!-- Só aparece em telas grandes (hidden lg:flex = mostra só em lg para cima) -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <!-- NavigationMenu é de ui/navigation-menu -->
                    <!-- Renderiza um menu horizontal com os links -->
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <!-- Itera sobre mainNavItems e cria um botão para cada um -->
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <Link :href="item.href">
                                    <NavigationMenuLink
                                        <!-- navigationMenuTriggerStyle vem de ui/navigation-menu e aplica estilo padrão -->
                                        <!-- activeItemStyles destaca o menu atual -->
                                        :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3']"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                        {{ item.title }}
                                    </NavigationMenuLink>
                                </Link>
                                <!-- Barra branca embaixo do menu ativo (feedback visual) -->
                                <div class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <!-- ===== LADO DIREITO DA BARRA (BUSCA, ÍCONES E USUÁRIO) ===== -->
                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <!-- Botão de busca (só visual por enquanto) -->
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer">
                            <Search class="size-5 opacity-80 group-hover:opacity-100" />
                        </Button>

                        <!-- Links à direita com ícones (repositório e documentação) -->
                        <!-- Só aparece em telas grandes -->
                        <div class="hidden space-x-1 lg:flex">
                                    <template v-for="item in itensNavegacaoDireita" :key="item.title">
                                <!-- TooltipProvider vem de ui/tooltip -->
                                <!-- Mostra um balãozinho quando passa mouse -->
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="ghost" size="icon" as-child class="group h-9 w-9 cursor-pointer">
                                                <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <!-- ===== MENU DO USUÁRIO =====-->
                    <!-- DropdownMenu vem de ui/dropdown-menu -->
                    <!-- Quando clica no avatar, aparece um menu com opções -->
                    <DropdownMenu>
                        <!-- DropdownMenuTrigger é o botão que abre o menu (o avatar) -->
                        <DropdownMenuTrigger :as-child="true">
                            <!-- Button é de ui/button -->
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <!-- Avatar é de ui/avatar - mostra a foto do usuário -->
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <!-- AvatarImage mostra a foto, AvatarFallback mostra as iniciais se não tiver foto -->
                                    <AvatarImage :src="autenticacao.user.avatar" :alt="autenticacao.user.name" />
                                    <!-- getIniciais pega as iniciais do nome (função de composables/useIniciais) -->
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getIniciais(autenticacao.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        
                        <!-- DropdownMenuContent é o menu que aparece -->
                        <DropdownMenuContent align="end" class="w-56">
                            <!-- UserMenuContent é nosso próprio componente que contém as opções do menu -->
                            <!-- Ele recebe os dados do usuário para mostrar -->
                            <ConteudoMenuUsuario :user="autenticacao.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <!-- ===== BREADCRUMBS (caminho de navegação) ===== -->
        <!-- Só aparece se tem breadcrumbs (tipo: Dashboard > Usuários > João) -->
        <div v-if="propriedades.migalhasNavegacao.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="propriedades.migalhasNavegacao" />
            </div>
        </div>
    </div>
</template>
