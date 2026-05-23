import { computed, nextTick, onMounted, reactive, ref } from 'vue';

/**
 * Essas estruturas abaixo definem os tipos TypeScript para as entidades principais do sistema de cadastro, como Repartição, Roteador e MAC, bem como para itens de combo e alertas. Esses tipos ajudam a garantir que os dados manipulados no sistema estejam estruturados corretamente e facilitam o desenvolvimento com autocompletar e verificação de tipos no editor.
 */

// Estrutura para represtar uma repartição
type Reparticao = {
    id: number;
    nome_contato: string;
    nome_reparticao: string;
    telefone: string;
    endereco: string;
    observacoes: string | null;
};

// Estrutura para representar um roteador
type Roteador = {
    id: number;
    ip_roteador: string;
    local_roteador: string;
    usuario: string;
    senha: string;
    reparticao_id: number;
    nome_reparticao: string | null;
};

// Estrutura para representar um MAC address
type Mac = {
    id: number;
    mac_address: string;
    nome_usuario: string;
    funcao_usuario: string | null;
    dispositivo: string | null;
    data_cadastro: string;
    ip_roteador: string | null;
    nome_reparticao: string | null;
    roteador_id: number;
};



/* Estrutura para itens de combo, que servem para armazenar opções de seleção para repartições e roteadores
   nos formulários, contendo um ID numérico e campos adicionais como nome, IP e repartição associada, que 
   podem ser usados para exibir informações relevantes nas opções de dropdown */
type ItemCombo = {
    id: number;
    nome?: string;
    ip?: string;
    reparticao?: string | null;
};



/* Estrutura para o estado do alerta, que controla a visibilidade, título, mensagem e tipo do alerta exibido 
   na interface, permitindo que o sistema mostre feedbacks claros e consistentes para o usuário em diferentes
   situações (sucesso, erro, aviso ou informação) */
type Alerta = {
    visivel: boolean;
    titulo: string;
    mensagem: string;
    tipo: 'sucesso' | 'erro' | 'aviso' | 'info';
};




// Valida se o item tem um ID numérico válido (número ou string numérica)
function temIdValido(item: unknown): item is { id: number } {
    if (!item || typeof item !== 'object') return false;

    const valorId = (item as { id?: unknown }).id;
    if (typeof valorId === 'number') return Number.isFinite(valorId);
    if (typeof valorId === 'string') return valorId.trim() !== '' && Number.isFinite(Number(valorId));

    return false;
}

/* Normaliza uma lista de itens, garantindo que cada item tenha um ID numérico válido e 
  convertendo IDs de string para número quando necessário. Itens sem ID válido são filtrados */
function normalizarListaComId<T extends { id: number }>(lista: unknown): T[] {
    if (!Array.isArray(lista)) return [];

    return lista
        .filter(temIdValido)
        .map((item) => ({ ...(item as T), id: Number((item as { id: number | string }).id) }));
}

// Esse export serve para deixar a função disponível para ser importada em outros arquivos, como componentes Vue, onde a lógica de cadastro será utilizada.
export function useSistemaCadastro() {
    // Essa variável controla qual aba está ativa no momento. O valor inicial é 'repartições'.
    const abaAtiva = ref<'reparticoes' | 'roteadores' | 'macs' | 'relatorios'>('reparticoes');

    // Ele guarda um objeto reativo que representa o estado do alerta, incluindo se ele está visível, o título, a mensagem e o tipo (sucesso, erro, aviso ou info).
    const alerta = reactive<Alerta>({
        visivel: false,
        titulo: '',
        mensagem: '',
        tipo: 'info',
    });

    // Aqui se guarda um objeto reativo que indica se os dados de repartições, roteadores e MACs estão sendo carregados, para mostrar indicadores de carregamento na interface.
    const carregando = reactive({
        reparticoes: false,
        roteadores: false,
        macs: false,
    });

    // Essa variável reativa armazena os filtros de texto para cada tipo de item (repartições, roteadores e MACs), permitindo que o usuário digite termos para filtrar as listas exibidas.
    const filtros = reactive({
        reparticoes: '',
        roteadores: '',
        macs: '',
    });

    // Essas variáveis reativas armazenam as listas de repartições, roteadores e MACs obtidas do servidor, que serão exibidas na interface e filtradas conforme os termos de busca.
    const reparticoes = ref<Reparticao[]>([]);
    const roteadores = ref<Roteador[]>([]);
    const macs = ref<Mac[]>([]);

    // Essa variável reativa armazena os itens atualmente selecionados para cada tipo (repartição, roteador e MAC)
    const selecionado = reactive<{
        reparticao: Reparticao | null;
        roteador: Roteador | null;
        mac: Mac | null;
    }>({
        reparticao: null,
        roteador: null,
        mac: null,
    });

    // Esses objetos reativos representam os formulários para criar ou editar repartições
    const formularioReparticao = reactive({
        nome_contato: '',
        nome_reparticao: '',
        telefone: '',
        endereco: '',
        observacoes: '',
    });

    // Diz se o formulário de repatições está em modo de edição
    const modoEdicaoReparticao = ref(false);

    // Quando existe um item selecionado, mas ainda não foi pedido o modo de edição, o formulário fica em leitura.
    const formularioReparticaoBloqueado = computed(() => Boolean(selecionado.reparticao) && !modoEdicaoReparticao.value);

    // Formulário para roteadores
    const formularioRoteador = reactive({
        ip_roteador: '',
        local_roteador: '',
        usuario: '',
        senha: '',
        reparticao_id: '',
    });

    // Diz se o formulário de roteadores está em modo de edição
    const modoEdicaoRoteador = ref(false);

    // Mesmo padrão da repartição: seleção sem edição deixa o formulário travado para evitar salvamento acidental.
    const formularioRoteadorBloqueado = computed(() => Boolean(selecionado.roteador) && !modoEdicaoRoteador.value);

    // Formulário para MACs
    const formularioMac = reactive({
        mac_address: '',
        nome_usuario: '',
        funcao_usuario: '',
        dispositivo: '',
        roteador_id: '',
    });

    // Diz se o formulário de MACs está em modo de edição
    const modoEdicaoMac = ref(false);

    // No cadastro de MACs, a seleção também entra em modo de consulta até o usuário clicar em editar.
    const formularioMacBloqueado = computed(() => Boolean(selecionado.mac) && !modoEdicaoMac.value);

    // Os combos servem para armazenar as opções de seleção para repartições e roteadores, que são carregadas do servidor e usadas em dropdowns nos formulários.
    const combos = reactive({
        reparticoes: [] as ItemCombo[],
        roteadores: [] as ItemCombo[],
    });

    // Essa variável reativa armazena o IP do roteador selecionado para gerar relatórios específicos, permitindo que o usuário escolha qual roteador deseja incluir no relatório.
    const ipRelatorioSelecionado = ref('');
    const exibirSenhaRoteador = ref(false);
    const campoObservacoesRef = ref<HTMLTextAreaElement | null>(null);

    // Essa função computada retorna a classe CSS apropriada para o alerta com base no tipo de alerta (sucesso, erro, aviso ou info), permitindo que a interface exiba o alerta com a aparência correta.
    const classeAlerta = computed(() => {
        if (alerta.tipo === 'sucesso') return 'rg-alerta-sucesso';
        if (alerta.tipo === 'erro') return 'rg-alerta-erro';
        if (alerta.tipo === 'aviso') return 'rg-alerta-aviso';
        return 'rg-alerta-info';
    });

    // Serve para normalizar o texto, removendo acentos, convertendo para minúsculas e removendo espaços extras, o que facilita a comparação e filtragem de strings.
    function normalizarTexto(valor?: string | null): string {
        return (valor || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    // Essa função verifica se o termo de busca é válido para filtrar as listas, exigindo que tenha pelo menos 4 caracteres (após normalização) ou seja vazio, para evitar filtros muito genéricos.
    function termoAutomaticoValido(valor?: string | null): string {
        const termo = normalizarTexto(valor);
        return termo.length >= 4 ? termo : '';
    }

    // Verifica se o filtro pode ser aplicado ou não, pois caso tenha menos de 4 caracteres, o sistema exigirá que o usuário digite mais caracteres
    function podeAplicarFiltro(valor?: string | null): boolean {
        const termo = normalizarTexto(valor);
        return termo.length === 0 || termo.length >= 4;
    }

    // Essa função remove todos os caracteres que não são dígitos de uma string, o que é útil para validar e formatar números de telefone, garantindo que apenas os números sejam considerados.
    function apenasDigitos(valor: string): string {
        return valor.replace(/\D/g, '');
    }

    // Essa função formata um número de telefone no formato brasileiro, adicionando parênteses para o DDD e hífens para separar os blocos de números, facilitando a leitura e garantindo um formato consistente.
    function formatarTelefoneBrasil(valor: string): string {
        const digitos = apenasDigitos(valor).slice(0, 11);

        if (digitos.length <= 2) return digitos;
        if (digitos.length <= 6) return `(${digitos.slice(0, 2)}) ${digitos.slice(2)}`;
        if (digitos.length <= 10) return `(${digitos.slice(0, 2)}) ${digitos.slice(2, 6)}-${digitos.slice(6)}`;

        return `(${digitos.slice(0, 2)}) ${digitos.slice(2, 7)}-${digitos.slice(7)}`;
    }

    // Essa função valida se um número de telefone é válido para o formato brasileiro, verificando se ele contém apenas dígitos e tem 10 ou 11 caracteres (considerando DDD e número), o que é necessário para garantir que os telefones cadastrados estejam corretos
    function validarTelefoneBrasil(valor: string): boolean {
        const tamanho = apenasDigitos(valor).length;
        return tamanho === 10 || tamanho === 11;
    }

    // Essa função é chamada quando o usuário digita no campo de telefone, formatando o valor em tempo real para o formato brasileiro
    function aoDigitarTelefone(evento: Event) {
        const alvo = evento.target as HTMLInputElement;
        formularioReparticao.telefone = formatarTelefoneBrasil(alvo.value);
    }

    // Verifica se um endereço IP está no formato IPv4 correto, verificando se ele consiste em quatro blocos de números entre 0 e 255, o que é essencial para garantir que os roteadores cadastrados tenham endereços IP válidos.
    function validarIpV4(valor: string): boolean {
        const ip = valor.trim();
        const blocos = ip.split('.');

        if (blocos.length !== 4) return false;

        return blocos.every((bloco) => {
            if (!/^\d+$/.test(bloco)) return false;
            if (bloco.length > 1 && bloco.startsWith('0')) return false;
            const numero = Number(bloco);
            return numero >= 0 && numero <= 255;
        });
    }

    // Verifica se o usuário digita no campo de IP, validando e formatando o valor em tempo real para garantir que ele esteja no formato IPv4 correto
    function formatarMac(valor: string): string {
        const hex = valor.replace(/[^0-9a-fA-F]/g, '').toUpperCase().slice(0, 12);
        const grupos: string[] = [];

        for (let i = 0; i < hex.length; i += 2) {
            grupos.push(hex.slice(i, i + 2));
        }

        return grupos.join(':');
    }

    // Valida se o endereço MAC está no formato correto
    function validarMac(valor: string): boolean {
        return /^([0-9A-F]{2}:){5}[0-9A-F]{2}$/.test(valor.trim().toUpperCase());
    }

    // Formata o endereço MAC em tempo real enquanto o usuário digita
    function aoDigitarMac(evento: Event) {
        const alvo = evento.target as HTMLInputElement;
        formularioMac.mac_address = formatarMac(alvo.value);
    }

    // Ajusta a altura do campo de observações para se adequar ao conteúdo
    function ajustarAlturaObservacoes() {
        const campo = campoObservacoesRef.value;

        if (!campo) return;

        campo.style.height = 'auto';

        const alturaMaxima = 280;
        const alturaCalculada = Math.min(campo.scrollHeight, alturaMaxima);

        campo.style.height = `${alturaCalculada}px`;
        campo.style.overflowY = campo.scrollHeight > alturaMaxima ? 'auto' : 'hidden';
    }

    // Essas funções computadas retornam as listas de resultados de busca que foram filtrados com base no que foi digitado pelo usuário 
    // Repartições:
    const reparticoesFiltradas = computed(() => {
        const termo = termoAutomaticoValido(filtros.reparticoes);
        if (!termo) return reparticoes.value;

        return reparticoes.value.filter((item) => {
            const textoItem = normalizarTexto(
                `${item.nome_contato} ${item.nome_reparticao} ${item.telefone} ${item.endereco} ${item.observacoes || ''}`,
            );
            return textoItem.includes(termo);
        });
    });

    // Roteadores:
    const roteadoresFiltrados = computed(() => {
        const termo = termoAutomaticoValido(filtros.roteadores);
        if (!termo) return roteadores.value;

        return roteadores.value.filter((item) => {
            const textoItem = normalizarTexto(`${item.ip_roteador} ${item.local_roteador} ${item.usuario} ${item.nome_reparticao || ''}`);
            return textoItem.includes(termo);
        });
    });

    // MACs:
    const macsFiltrados = computed(() => {
        const termo = termoAutomaticoValido(filtros.macs);
        if (!termo) return macs.value;

        return macs.value.filter((item) => {
            const textoItem = normalizarTexto(
                `${item.mac_address} ${item.nome_usuario} ${item.funcao_usuario || ''} ${item.dispositivo || ''} ${item.ip_roteador || ''} ${item.nome_reparticao || ''}`,
            );
            return textoItem.includes(termo);
        });
    });

    // Mostra alerta com o título, mensagem e tipo (sucesso, erro, aviso ou info) especificados, e oculta o alerta automaticamente após 4 segundos
    function mostrarAlerta(titulo: string, mensagem: string, tipo: Alerta['tipo'] = 'info') {
        alerta.titulo = titulo;
        alerta.mensagem = mensagem;
        alerta.tipo = tipo;
        alerta.visivel = true;
        window.setTimeout(() => {
            alerta.visivel = false;
        }, 4000);
    }

    /**
     * Essas funções abaixo são responsáveis por aplicar os filtros de busca para cada tipo de item 
     * (repartições, roteadores e MACs), verificando se o termo de busca é válido antes de atualizar 
     * as listas filtradas. Se o termo for inválido, um alerta é exibido solicitando que o usuário 
     * digite pelo menos 4 caracteres para filtrar. Caso contrário, as listas filtradas são atualizadas 
     * com base no termo de busca
     */
    
    // Repartições:
    function aplicarFiltroReparticoes() {
        if (!podeAplicarFiltro(filtros.reparticoes)) {
            mostrarAlerta('Aviso', 'Digite pelo menos 4 caracteres para filtrar.', 'aviso');
            return;
        }

        atualizarReparticoes();
    }

    // Roteadores:
    function aplicarFiltroRoteadores() {
        if (!podeAplicarFiltro(filtros.roteadores)) {
            mostrarAlerta('Aviso', 'Digite pelo menos 4 caracteres para filtrar.', 'aviso');
            return;
        }

        atualizarRoteadores();
    }

    // MACs:
    function aplicarFiltroMacs() {
        if (!podeAplicarFiltro(filtros.macs)) {
            mostrarAlerta('Aviso', 'Digite pelo menos 4 caracteres para filtrar.', 'aviso');
            return;
        }

        atualizarMacs();
    }

    /**
     * Essa função é uma abstração para fazer requisições HTTP usando fetch, configurando os cabeçalhos 
     * para JSON e tratando erros de forma consistente. Ela é usada para todas as operações de CRUD 
     * (criar, ler, atualizar, excluir) para repartições, roteadores e MACs, garantindo que as respostas
     * sejam tratadas corretamente e que mensagens de erro sejam exibidas quando necessário
     */
    async function requisicaoJson<T>(url: string, opcoes?: RequestInit): Promise<T> {
        const resposta = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            ...opcoes,
        });

        if (!resposta.ok) {
            let mensagem = 'Erro na requisicao.';

            try {
                const erro = await resposta.json();
                mensagem = erro.message || erro.mensagem || mensagem;
            } catch {
                mensagem = await resposta.text();
            }

            throw new Error(mensagem);
        }

        if (resposta.status === 204) {
            return null as T;
        }

        return (await resposta.json()) as T;
    }

    /* Essas funções abaixo se referem as funcionalidades referentes às repartições */
    
   // Atualizar 
    async function atualizarReparticoes() {
        carregando.reparticoes = true;

        try {
            const dados = await requisicaoJson<unknown>('/api/reparticoes');
            reparticoes.value = normalizarListaComId<Reparticao>(dados);
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        } finally {
            carregando.reparticoes = false;
        }
    }

    // Preenche o formulário de repartição com os dados do item selecionado, permitindo que o usuário veja e edite as informações da repartição escolhida
    function preencherFormularioReparticao(item: Reparticao) {
        formularioReparticao.nome_contato = item.nome_contato;
        formularioReparticao.nome_reparticao = item.nome_reparticao;
        formularioReparticao.telefone = item.telefone;
        formularioReparticao.endereco = item.endereco;
        formularioReparticao.observacoes = item.observacoes || '';
        nextTick(() => ajustarAlturaObservacoes());
    }

    // Limpa apenas os campos do formulário, sem mexer no item selecionado.
    function limparCamposReparticao() {
        formularioReparticao.nome_contato = '';
        formularioReparticao.nome_reparticao = '';
        formularioReparticao.telefone = '';
        formularioReparticao.endereco = '';
        formularioReparticao.observacoes = '';
        nextTick(() => ajustarAlturaObservacoes());
    }

    // Selecionar Repartição específica para edição ou visualização
    function selecionarReparticao(item: Reparticao) {
        selecionado.reparticao = item;
        modoEdicaoReparticao.value = false;
        limparCamposReparticao();
    }

    // Limpar o formulário da aba de repartições
    function limparFormularioReparticao() {
        selecionado.reparticao = null;
        modoEdicaoReparticao.value = false;
        limparCamposReparticao();
    }

    // Editar item repartição
    function editarReparticao() {
        if (!selecionado.reparticao) {
            mostrarAlerta('Aviso', 'Selecione uma reparticao para editar.', 'aviso');
            return;
        }

        modoEdicaoReparticao.value = true;
        preencherFormularioReparticao(selecionado.reparticao);
    }

    // Salvar item repartição
    async function salvarReparticao() {
        if (formularioReparticaoBloqueado.value) {
            mostrarAlerta('Aviso', 'Selecione editar antes de salvar essa reparticao.', 'aviso');
            return;
        }

        if (!formularioReparticao.nome_contato || !formularioReparticao.nome_reparticao || !formularioReparticao.telefone || !formularioReparticao.endereco) {
            mostrarAlerta('Validacao', 'Preencha os campos obrigatorios.', 'aviso');
            return;
        }

        if (!validarTelefoneBrasil(formularioReparticao.telefone)) {
            mostrarAlerta('Validacao', 'Telefone invalido. Use formato brasileiro com DDD.', 'aviso');
            return;
        }

        try {
            if (modoEdicaoReparticao.value && selecionado.reparticao) {
                await requisicaoJson(`/api/reparticoes/${selecionado.reparticao.id}`, {
                    method: 'PUT',
                    body: JSON.stringify(formularioReparticao),
                });

                mostrarAlerta('Sucesso', 'Reparticao atualizada com sucesso!', 'sucesso');
            } else {
                await requisicaoJson('/api/reparticoes', {
                    method: 'POST',
                    body: JSON.stringify(formularioReparticao),
                });

                mostrarAlerta('Sucesso', 'Reparticao cadastrada com sucesso!', 'sucesso');
            }
            limparFormularioReparticao();
            await atualizarReparticoes();
            await carregarComboReparticoes();
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    // Excluir item repartição
    async function excluirReparticao() {
        if (!selecionado.reparticao) {
            mostrarAlerta('Aviso', 'Selecione uma reparticao para excluir.', 'aviso');
            return;
        }

        if (!window.confirm(`Excluir reparticao '${selecionado.reparticao.nome_reparticao}'?`)) {
            return;
        }

        try {
            await requisicaoJson(`/api/reparticoes/${selecionado.reparticao.id}`, {
                method: 'DELETE',
            });

            mostrarAlerta('Sucesso', 'Reparticao excluida com sucesso!', 'sucesso');
            limparFormularioReparticao();
            await atualizarReparticoes();
            await carregarComboReparticoes();
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    // Carrega as opções de repartições para os combos de seleção nos formulários, garantindo que o usuário tenha as opções mais recentes disponíveis ao cadastrar
    async function carregarComboReparticoes() {
        try {
            const dados = await requisicaoJson<unknown>('/api/reparticoes/combo');
            combos.reparticoes = normalizarListaComId<ItemCombo>(dados);

            if (!formularioRoteador.reparticao_id && combos.reparticoes.length > 0) {
                formularioRoteador.reparticao_id = String(combos.reparticoes[0].id);
            }
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    /** 
     * Essas funções abaixo se referem as funcionalidades referentes aos roteadores, seguindo a mesma lógica das repartições para manter a consistência na interface e na experiência do usuário
     * 
     */
    
    // Atualizar a lista de roteadores
    async function atualizarRoteadores() {
        carregando.roteadores = true;

        try {
            const dados = await requisicaoJson<unknown>('/api/roteadores');
            roteadores.value = normalizarListaComId<Roteador>(dados);
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        } finally {
            carregando.roteadores = false;
        }
    }

    // Preenche o formulário de roteador com os dados do item selecionado, permitindo que o usuário veja e edite as informações do roteador escolhido
    function preencherFormularioRoteador(item: Roteador) {
        formularioRoteador.ip_roteador = item.ip_roteador;
        formularioRoteador.local_roteador = item.local_roteador;
        formularioRoteador.usuario = item.usuario;
        formularioRoteador.senha = item.senha;
        formularioRoteador.reparticao_id = String(item.reparticao_id);
    }

    // Limpa apenas os campos do roteador, mantendo a seleção da lista.
    function limparCamposRoteador() {
        formularioRoteador.ip_roteador = '';
        formularioRoteador.local_roteador = '';
        formularioRoteador.usuario = '';
        formularioRoteador.senha = '';
        formularioRoteador.reparticao_id = combos.reparticoes.length ? String(combos.reparticoes[0].id) : '';
    }

    // Selecionar Roteador específico para edição ou visualização
    function selecionarRoteador(item: Roteador) {
        selecionado.roteador = item;
        modoEdicaoRoteador.value = false;
        limparCamposRoteador();
    }

    // Limpar o formulário da aba de roteadores
    function limparFormularioRoteador() {
        selecionado.roteador = null;
        modoEdicaoRoteador.value = false;
        limparCamposRoteador();
    }

    // Editar item roteador
    function editarRoteador() {
        if (!selecionado.roteador) {
            mostrarAlerta('Aviso', 'Selecione um roteador para editar.', 'aviso');
            return;
        }

        modoEdicaoRoteador.value = true;
        preencherFormularioRoteador(selecionado.roteador);
    }

    // Salvar item roteador
    async function salvarRoteador() {
        if (formularioRoteadorBloqueado.value) {
            mostrarAlerta('Aviso', 'Selecione editar antes de salvar esse roteador.', 'aviso');
            return;
        }

        if (!formularioRoteador.ip_roteador || !formularioRoteador.local_roteador || !formularioRoteador.usuario || !formularioRoteador.senha || !formularioRoteador.reparticao_id) {
            mostrarAlerta('Validacao', 'Preencha os campos obrigatorios.', 'aviso');
            return;
        }

        if (!validarIpV4(formularioRoteador.ip_roteador)) {
            mostrarAlerta('Validacao', 'IP invalido. Use IPv4 no formato 192.168.0.1.', 'aviso');
            return;
        }

        try {
            const dadosRoteador = {
                ...formularioRoteador,
                reparticao_id: Number(formularioRoteador.reparticao_id),
            };

            if (modoEdicaoRoteador.value && selecionado.roteador) {
                await requisicaoJson(`/api/roteadores/${selecionado.roteador.id}`, {
                    method: 'PUT',
                    body: JSON.stringify(dadosRoteador),
                });

                mostrarAlerta('Sucesso', 'Roteador atualizado com sucesso!', 'sucesso');
            } else {
                await requisicaoJson('/api/roteadores', {
                    method: 'POST',
                    body: JSON.stringify(dadosRoteador),
                });

                mostrarAlerta('Sucesso', 'Roteador cadastrado com sucesso!', 'sucesso');
            }

            limparFormularioRoteador();
            await atualizarRoteadores();
            await carregarComboRoteadores();
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }
    
    // Excluir item roteador
    async function excluirRoteador() {
        if (!selecionado.roteador) {
            mostrarAlerta('Aviso', 'Selecione um roteador para excluir.', 'aviso');
            return;
        }

        if (!window.confirm(`Excluir roteador '${selecionado.roteador.ip_roteador}'?`)) {
            return;
        }

        try {
            await requisicaoJson(`/api/roteadores/${selecionado.roteador.id}`, {
                method: 'DELETE',
            });

            mostrarAlerta('Sucesso', 'Roteador excluido com sucesso!', 'sucesso');
            limparFormularioRoteador();
            await atualizarRoteadores();
            await carregarComboRoteadores();
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    // Carrega as opções de roteadores para os combos de seleção nos formulários, garantindo que o usuário tenha as opções mais recentes disponíveis ao cadastrar
    async function carregarComboRoteadores() {
        try {
            const dados = await requisicaoJson<unknown>('/api/roteadores/combo');
            combos.roteadores = normalizarListaComId<ItemCombo>(dados);

            if (!formularioMac.roteador_id && combos.roteadores.length > 0) {
                formularioMac.roteador_id = String(combos.roteadores[0].id);
                ipRelatorioSelecionado.value = combos.roteadores[0].ip || '';
            }

            if (!ipRelatorioSelecionado.value && combos.roteadores.length > 0) {
                ipRelatorioSelecionado.value = combos.roteadores[0].ip || '';
            }
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    /**
     * Essas funções abaixo se referem as funcionalidades referentes aos MACs, seguindo a mesma lógica das repartições e roteadores para manter a consistência na interface e na experiência do usuário
     */
    // Atualizar a lista de MACs
    async function atualizarMacs() {
        carregando.macs = true;

        try {
            const dados = await requisicaoJson<unknown>('/api/macs');
            macs.value = normalizarListaComId<Mac>(dados);
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        } finally {
            carregando.macs = false;
        }
    }

    // Preenche o formulário de MAC com os dados do item selecionado, permitindo que o usuário veja e edite as informações do MAC escolhido
    function preencherFormularioMac(item: Mac) {
        formularioMac.mac_address = formatarMac(item.mac_address);
        formularioMac.nome_usuario = item.nome_usuario;
        formularioMac.funcao_usuario = item.funcao_usuario || '';
        formularioMac.dispositivo = item.dispositivo || '';
        formularioMac.roteador_id = String(item.roteador_id);
    }

    // Limpa apenas os campos do MAC, sem mexer no item atualmente selecionado.
    function limparCamposMac() {
        formularioMac.mac_address = '';
        formularioMac.nome_usuario = '';
        formularioMac.funcao_usuario = '';
        formularioMac.dispositivo = '';
        formularioMac.roteador_id = combos.roteadores.length ? String(combos.roteadores[0].id) : '';
    }

    // Selecionar MAC específico para edição ou visualização
    function selecionarMac(item: Mac) {
        selecionado.mac = item;
        modoEdicaoMac.value = false;
        limparCamposMac();
    }

    // Limpar o formulário da aba de MACs
    function limparFormularioMac() {
        selecionado.mac = null;
        modoEdicaoMac.value = false;
        limparCamposMac();
    }

    // Editar item MAC
    function editarMac() {
        if (!selecionado.mac) {
            mostrarAlerta('Aviso', 'Selecione um MAC para editar.', 'aviso');
            return;
        }

        modoEdicaoMac.value = true;
        preencherFormularioMac(selecionado.mac);
    }

    // Salvar item MAC
    async function salvarMac() {
        if (formularioMacBloqueado.value) {
            mostrarAlerta('Aviso', 'Selecione editar antes de salvar esse MAC.', 'aviso');
            return;
        }

        if (!formularioMac.mac_address || !formularioMac.nome_usuario || !formularioMac.roteador_id) {
            mostrarAlerta('Validacao', 'Preencha os campos obrigatorios.', 'aviso');
            return;
        }

        const macFormatado = formatarMac(formularioMac.mac_address);
        formularioMac.mac_address = macFormatado;

        if (!validarMac(macFormatado)) {
            mostrarAlerta('Validacao', 'MAC invalido. Use formato AA:BB:CC:DD:EE:FF.', 'aviso');
            return;
        }

        try {
            const dadosMac = {
                ...formularioMac,
                roteador_id: Number(formularioMac.roteador_id),
            };

            if (modoEdicaoMac.value && selecionado.mac) {
                await requisicaoJson(`/api/macs/${selecionado.mac.id}`, {
                    method: 'PUT',
                    body: JSON.stringify(dadosMac),
                });

                mostrarAlerta('Sucesso', 'MAC address atualizado com sucesso!', 'sucesso');
            } else {
                await requisicaoJson('/api/macs', {
                    method: 'POST',
                    body: JSON.stringify(dadosMac),
                });

                mostrarAlerta('Sucesso', 'MAC address cadastrado com sucesso!', 'sucesso');
            }

            limparFormularioMac();
            await atualizarMacs();
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    // Excluir item MAC
    async function excluirMac() {
        if (!selecionado.mac) {
            mostrarAlerta('Aviso', 'Selecione um MAC para excluir.', 'aviso');
            return;
        }

        if (!window.confirm(`Excluir MAC '${selecionado.mac.mac_address}'?`)) {
            return;
        }

        try {
            await requisicaoJson(`/api/macs/${selecionado.mac.id}`, {
                method: 'DELETE',
            });

            mostrarAlerta('Sucesso', 'MAC address excluido com sucesso!', 'sucesso');
            limparFormularioMac();
            await atualizarMacs();
        } catch (erro) {
            mostrarAlerta('Erro', (erro as Error).message, 'erro');
        }
    }

    /**
     * Essas funções abaixo se referem as funcionalidades de geração de relatórios, permitindo que o usuário
     * gere relatórios em PDF ou Excel para repartições, roteadores e MACs, e que os relatórios sejam abertos 
     * em uma nova aba do navegador para visualização ou download
     */
    function abrirRelatorio(url: string) {
        window.open(url, '_blank', 'noopener,noreferrer');
    }

    function gerarRelatorioReparticoesPdf() {
        abrirRelatorio('/api/relatorios/reparticoes/pdf');
    }

    function gerarRelatorioReparticoesExcel() {
        abrirRelatorio('/api/relatorios/reparticoes/excel');
    }

    function gerarRelatorioRoteadorPdf() {
        if (!ipRelatorioSelecionado.value) {
            mostrarAlerta('Aviso', 'Selecione um roteador.', 'aviso');
            return;
        }

        abrirRelatorio(`/api/relatorios/roteador/${encodeURIComponent(ipRelatorioSelecionado.value)}/pdf`);
    }

    function gerarRelatorioRoteadorExcel() {
        if (!ipRelatorioSelecionado.value) {
            mostrarAlerta('Aviso', 'Selecione um roteador.', 'aviso');
            return;
        }

        abrirRelatorio(`/api/relatorios/roteador/${encodeURIComponent(ipRelatorioSelecionado.value)}/excel`);
    }

    function gerarRelatorioMacsPdf() {
        abrirRelatorio('/api/relatorios/macs/pdf');
    }

    function gerarRelatorioMacsExcel() {
        abrirRelatorio('/api/relatorios/macs/excel');
    }


    // Essa função é chamada quando o usuário troca de aba, atualizando a variável que controla a aba ativa e carregando os dados correspondentes para a aba selecionada, garantindo que as informações exibidas estejam sempre atualizadas quando o usuário navegar entre as abas
    function trocarAba(aba: typeof abaAtiva.value) {
        abaAtiva.value = aba;

        if (aba === 'roteadores') {
            atualizarRoteadores();
        }

        if (aba === 'macs') {
            carregarComboRoteadores();
            atualizarMacs();
        }

        if (aba === 'relatorios') {
            carregarComboRoteadores();
        }
    }

    onMounted(async () => {
        await atualizarReparticoes();
        await carregarComboReparticoes();
        await carregarComboRoteadores();
        ajustarAlturaObservacoes();
    });

    return {
        alerta,
        abaAtiva,
        selecionado,
        carregarComboReparticoes,
        carregarComboRoteadores,
        classeAlerta,
        combos,
        carregando,
        campoObservacoesRef,
        exibirSenhaRoteador,
        filtros,
        formularioMacBloqueado,
        formularioReparticaoBloqueado,
        formularioRoteadorBloqueado,
        formularioMac,
        formularioReparticao,
        formularioRoteador,
        ipRelatorioSelecionado,
        limparFormularioMac,
        limparFormularioReparticao,
        limparFormularioRoteador,
        modoEdicaoMac,
        modoEdicaoReparticao,
        modoEdicaoRoteador,
        macsFiltrados,
        mostrarAlerta,
        reparticoes,
        reparticoesFiltradas,
        roteadores: roteadores,
        roteadoresFiltrados,
        macs,
        atualizarReparticoes,
        atualizarRoteadores,
        atualizarMacs,
        selecionarMac,
        selecionarReparticao,
        selecionarRoteador,
        salvarMac,
        salvarReparticao,
        salvarRoteador,
        editarMac,
        editarReparticao,
        editarRoteador,
        excluirMac,
        excluirReparticao,
        excluirRoteador,
        aplicarFiltroReparticoes,
        aplicarFiltroRoteadores,
        aplicarFiltroMacs,
        ajustarAlturaObservacoes,
        aoDigitarMac,
        aoDigitarTelefone,
        gerarRelatorioMacsExcel,
        gerarRelatorioMacsPdf,
        gerarRelatorioReparticoesExcel,
        gerarRelatorioReparticoesPdf,
        gerarRelatorioRoteadorExcel,
        gerarRelatorioRoteadorPdf,
        trocarAba,
    };
}

/**
 * Essa linha define um tipo TypeScript para o estado do sistema de cadastro, que é o retorno da função 
 * useSistemaCadastro, permitindo que outras partes do código possam usar esse tipo para garantir a consistência
 * e a segurança de tipos ao acessar o estado e as funções fornecidas por esse composable
 */
export type SistemaCadastroState = ReturnType<typeof useSistemaCadastro>;