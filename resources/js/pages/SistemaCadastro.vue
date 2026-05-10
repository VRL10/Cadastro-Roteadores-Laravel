<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref } from 'vue';

type Reparticao = {
    id: number;
    nome_contato: string;
    nome_reparticao: string;
    telefone: string;
    endereco: string;
    observacoes: string | null;
};

type Roteador = {
    id: number;
    ip_roteador: string;
    local_roteador: string;
    usuario: string;
    senha: string;
    reparticao_id: number;
    nome_reparticao: string | null;
};

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

type ItemCombo = {
    id: number;
    nome?: string;
    ip?: string;
    reparticao?: string | null;
};

type Alerta = {
    visivel: boolean;
    titulo: string;
    mensagem: string;
    tipo: 'sucesso' | 'erro' | 'aviso' | 'info';
};

const abaAtiva = ref<'reparticoes' | 'roteadores' | 'macs' | 'relatorios'>('reparticoes');

const alerta = reactive<Alerta>({
    visivel: false,
    titulo: '',
    mensagem: '',
    tipo: 'info',
});

const carregando = reactive({
    reparticoes: false,
    roteadores: false,
    macs: false,
});

const filtros = reactive({
    reparticoes: '',
    roteadores: '',
    macs: '',
});

const reparticoes = ref<Reparticao[]>([]);
const roteadores = ref<Roteador[]>([]);
const macs = ref<Mac[]>([]);

const selecionado = reactive<{
    reparticao: Reparticao | null;
    roteador: Roteador | null;
    mac: Mac | null;
}>({
    reparticao: null,
    roteador: null,
    mac: null,
});

const formularioReparticao = reactive({
    nome_contato: '',
    nome_reparticao: '',
    telefone: '',
    endereco: '',
    observacoes: '',
});

const modoEdicaoReparticao = ref(false);

const formularioRoteador = reactive({
    ip_roteador: '',
    local_roteador: '',
    usuario: '',
    senha: '',
    reparticao_id: '',
});

const modoEdicaoRoteador = ref(false);

const formularioMac = reactive({
    mac_address: '',
    nome_usuario: '',
    funcao_usuario: '',
    dispositivo: '',
    roteador_id: '',
});

const modoEdicaoMac = ref(false);

const combos = reactive({
    reparticoes: [] as ItemCombo[],
    roteadores: [] as ItemCombo[],
});

const ipRelatorioSelecionado = ref('');

const classeAlerta = computed(() => {
    if (alerta.tipo === 'sucesso') return 'rg-alerta-sucesso';
    if (alerta.tipo === 'erro') return 'rg-alerta-erro';
    if (alerta.tipo === 'aviso') return 'rg-alerta-aviso';
    return 'rg-alerta-info';
});

function mostrarAlerta(titulo: string, mensagem: string, tipo: Alerta['tipo'] = 'info') {
    alerta.titulo = titulo;
    alerta.mensagem = mensagem;
    alerta.tipo = tipo;
    alerta.visivel = true;
    window.setTimeout(() => {
        alerta.visivel = false;
    }, 4000);
}

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

async function atualizarReparticoes() {
    carregando.reparticoes = true;

    try {
        const termo = filtros.reparticoes.trim();
        const query = termo ? `?filtro=${encodeURIComponent(termo)}` : '';
        reparticoes.value = await requisicaoJson<Reparticao[]>(`/api/reparticoes${query}`);
    } catch (erro) {
        mostrarAlerta('Erro', (erro as Error).message, 'erro');
    } finally {
        carregando.reparticoes = false;
    }
}

function preencherFormularioReparticao(item: Reparticao) {
    formularioReparticao.nome_contato = item.nome_contato;
    formularioReparticao.nome_reparticao = item.nome_reparticao;
    formularioReparticao.telefone = item.telefone;
    formularioReparticao.endereco = item.endereco;
    formularioReparticao.observacoes = item.observacoes || '';
}

function selecionarReparticao(item: Reparticao) {
    selecionado.reparticao = item;
    modoEdicaoReparticao.value = false;
}

function limparFormularioReparticao() {
    selecionado.reparticao = null;
    modoEdicaoReparticao.value = false;
    formularioReparticao.nome_contato = '';
    formularioReparticao.nome_reparticao = '';
    formularioReparticao.telefone = '';
    formularioReparticao.endereco = '';
    formularioReparticao.observacoes = '';
}

function editarReparticao() {
    if (!selecionado.reparticao) {
        mostrarAlerta('Aviso', 'Selecione uma reparticao para editar.', 'aviso');
        return;
    }

    modoEdicaoReparticao.value = true;
    preencherFormularioReparticao(selecionado.reparticao);
}

async function salvarReparticao() {
    if (!formularioReparticao.nome_contato || !formularioReparticao.nome_reparticao || !formularioReparticao.telefone || !formularioReparticao.endereco) {
        mostrarAlerta('Validacao', 'Preencha os campos obrigatorios.', 'aviso');
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

async function carregarComboReparticoes() {
    try {
        combos.reparticoes = await requisicaoJson<ItemCombo[]>('/api/reparticoes/combo');

        if (!formularioRoteador.reparticao_id && combos.reparticoes.length > 0) {
            formularioRoteador.reparticao_id = String(combos.reparticoes[0].id);
        }
    } catch (erro) {
        mostrarAlerta('Erro', (erro as Error).message, 'erro');
    }
}

async function atualizarRoteadores() {
    carregando.roteadores = true;

    try {
        const termo = filtros.roteadores.trim();
        const query = termo ? `?filtro=${encodeURIComponent(termo)}` : '';
        roteadores.value = await requisicaoJson<Roteador[]>(`/api/roteadores${query}`);
    } catch (erro) {
        mostrarAlerta('Erro', (erro as Error).message, 'erro');
    } finally {
        carregando.roteadores = false;
    }
}

function preencherFormularioRoteador(item: Roteador) {
    formularioRoteador.ip_roteador = item.ip_roteador;
    formularioRoteador.local_roteador = item.local_roteador;
    formularioRoteador.usuario = item.usuario;
    formularioRoteador.senha = item.senha;
    formularioRoteador.reparticao_id = String(item.reparticao_id);
}

function selecionarRoteador(item: Roteador) {
    selecionado.roteador = item;
    modoEdicaoRoteador.value = false;
}

function limparFormularioRoteador() {
    selecionado.roteador = null;
    modoEdicaoRoteador.value = false;
    formularioRoteador.ip_roteador = '';
    formularioRoteador.local_roteador = '';
    formularioRoteador.usuario = '';
    formularioRoteador.senha = '';
    formularioRoteador.reparticao_id = combos.reparticoes.length ? String(combos.reparticoes[0].id) : '';
}

function editarRoteador() {
    if (!selecionado.roteador) {
        mostrarAlerta('Aviso', 'Selecione um roteador para editar.', 'aviso');
        return;
    }

    modoEdicaoRoteador.value = true;
    preencherFormularioRoteador(selecionado.roteador);
}

async function salvarRoteador() {
    if (!formularioRoteador.ip_roteador || !formularioRoteador.local_roteador || !formularioRoteador.usuario || !formularioRoteador.senha || !formularioRoteador.reparticao_id) {
        mostrarAlerta('Validacao', 'Preencha os campos obrigatorios.', 'aviso');
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

async function carregarComboRoteadores() {
    try {
        combos.roteadores = await requisicaoJson<ItemCombo[]>('/api/roteadores/combo');

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

async function atualizarMacs() {
    carregando.macs = true;

    try {
        const termo = filtros.macs.trim();
        const query = termo ? `?filtro=${encodeURIComponent(termo)}` : '';
        macs.value = await requisicaoJson<Mac[]>(`/api/macs${query}`);
    } catch (erro) {
        mostrarAlerta('Erro', (erro as Error).message, 'erro');
    } finally {
        carregando.macs = false;
    }
}

function preencherFormularioMac(item: Mac) {
    formularioMac.mac_address = item.mac_address;
    formularioMac.nome_usuario = item.nome_usuario;
    formularioMac.funcao_usuario = item.funcao_usuario || '';
    formularioMac.dispositivo = item.dispositivo || '';
    formularioMac.roteador_id = String(item.roteador_id);
}

function selecionarMac(item: Mac) {
    selecionado.mac = item;
    modoEdicaoMac.value = false;
}

function limparFormularioMac() {
    selecionado.mac = null;
    modoEdicaoMac.value = false;
    formularioMac.mac_address = '';
    formularioMac.nome_usuario = '';
    formularioMac.funcao_usuario = '';
    formularioMac.dispositivo = '';
    formularioMac.roteador_id = combos.roteadores.length ? String(combos.roteadores[0].id) : '';
}

function editarMac() {
    if (!selecionado.mac) {
        mostrarAlerta('Aviso', 'Selecione um MAC para editar.', 'aviso');
        return;
    }

    modoEdicaoMac.value = true;
    preencherFormularioMac(selecionado.mac);
}

async function salvarMac() {
    if (!formularioMac.mac_address || !formularioMac.nome_usuario || !formularioMac.roteador_id) {
        mostrarAlerta('Validacao', 'Preencha os campos obrigatorios.', 'aviso');
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
});
</script>

<template>
    <Head title="Sistema de Cadastro" />

    <div class="rg-pagina">
        <div class="rg-cabecalho">
            <div class="rg-cabecalho-gradiente">
                <h1>🏢 Sistema de Cadastro - Repartições e Roteadores</h1>
            </div>
        </div>

        <div class="rg-conteudo">
            <div v-if="alerta.visivel" class="rg-alerta" :class="classeAlerta">
                <strong>{{ alerta.titulo }}</strong>
                <span>{{ alerta.mensagem }}</span>
            </div>

            <nav class="rg-abas" aria-label="Navegação principal">
                <button class="rg-aba" :class="{ ativo: abaAtiva === 'reparticoes' }" @click="trocarAba('reparticoes')">🏢 Repartições</button>
                <button class="rg-aba" :class="{ ativo: abaAtiva === 'roteadores' }" @click="trocarAba('roteadores')">📡 Roteadores</button>
                <button class="rg-aba" :class="{ ativo: abaAtiva === 'macs' }" @click="trocarAba('macs')">🔗 MAC Addresses</button>
                <button class="rg-aba" :class="{ ativo: abaAtiva === 'relatorios' }" @click="trocarAba('relatorios')">📊 Relatórios</button>
            </nav>

            <section v-show="abaAtiva === 'reparticoes'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>📝 Cadastrar/Editar Repartição</h3>
                    <div class="rg-grid-2">
                        <div>
                            <label>👤 Nome do Contato</label>
                            <input v-model="formularioReparticao.nome_contato" type="text" />
                        </div>
                        <div>
                            <label>🏢 Nome da Repartição</label>
                            <input v-model="formularioReparticao.nome_reparticao" type="text" />
                        </div>
                        <div>
                            <label>📞 Telefone</label>
                            <input v-model="formularioReparticao.telefone" type="text" />
                        </div>
                        <div>
                            <label>📍 Endereço</label>
                            <input v-model="formularioReparticao.endereco" type="text" />
                        </div>
                    </div>
                    <div>
                        <label>📝 Observações</label>
                        <input v-model="formularioReparticao.observacoes" type="text" />
                    </div>
                    <div class="rg-acoes">
                        <button class="btn-sucesso" @click="salvarReparticao">💾 {{ modoEdicaoReparticao ? 'SALVAR ALTERAÇÕES' : 'SALVAR' }}</button>
                        <button class="btn-aviso" @click="editarReparticao">✏️ EDITAR</button>
                        <button class="btn-erro" @click="excluirReparticao">🗑️ EXCLUIR</button>
                        <button class="btn-info" @click="atualizarReparticoes">🔄 ATUALIZAR</button>
                        <button class="btn-secundario" @click="limparFormularioReparticao">🧹 LIMPAR</button>
                    </div>
                </div>

                <div class="rg-card rg-card-filtro">
                    <h3>🔍 Filtro</h3>
                    <div class="rg-grid-filtro">
                        <input v-model="filtros.reparticoes" type="text" placeholder="Buscar por contato, repartição, telefone..." @keyup.enter="atualizarReparticoes" />
                        <div class="rg-botoes-coluna">
                            <button class="btn-primario" @click="atualizarReparticoes">🔍 Aplicar Filtro</button>
                            <button class="btn-secundario" @click="() => { filtros.reparticoes = ''; atualizarReparticoes(); }">🗑️ Limpar Filtro</button>
                        </div>
                    </div>
                </div>

                <div class="rg-card rg-card-lista">
                    <h3>📋 Repartições Cadastradas</h3>
                    <div class="rg-tabela-wrap">
                        <table class="rg-tabela">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Contato</th>
                                    <th>Repartição</th>
                                    <th>Telefone</th>
                                    <th>Endereço</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!carregando.reparticoes && reparticoes.length === 0">
                                    <td colspan="6">Nenhuma repartição cadastrada.</td>
                                </tr>
                                <tr
                                    v-for="item in reparticoes"
                                    :key="item.id"
                                    :class="{ selecionada: selecionado.reparticao?.id === item.id }"
                                    @click="selecionarReparticao(item)"
                                >
                                    <td>{{ item.id }}</td>
                                    <td>{{ item.nome_contato }}</td>
                                    <td>{{ item.nome_reparticao }}</td>
                                    <td>{{ item.telefone }}</td>
                                    <td>{{ item.endereco }}</td>
                                    <td>{{ item.observacoes || '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-show="abaAtiva === 'roteadores'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>📝 Cadastrar/Editar Roteador</h3>
                    <div class="rg-grid-2">
                        <div>
                            <label>🌐 IP do Roteador</label>
                            <input v-model="formularioRoteador.ip_roteador" type="text" placeholder="192.168.1.1" />
                        </div>
                        <div>
                            <label>📍 Local do Roteador</label>
                            <input v-model="formularioRoteador.local_roteador" type="text" />
                        </div>
                        <div>
                            <label>👤 Usuário</label>
                            <input v-model="formularioRoteador.usuario" type="text" />
                        </div>
                        <div>
                            <label>🔑 Senha</label>
                            <input v-model="formularioRoteador.senha" type="password" />
                        </div>
                    </div>
                    <div>
                        <label>🏢 Repartição</label>
                        <select v-model="formularioRoteador.reparticao_id">
                            <option v-for="item in combos.reparticoes" :key="item.id" :value="String(item.id)">
                                {{ item.id }} - {{ item.nome }}
                            </option>
                        </select>
                    </div>
                    <div class="rg-acoes">
                        <button class="btn-sucesso" @click="salvarRoteador">💾 {{ modoEdicaoRoteador ? 'SALVAR ALTERAÇÕES' : 'SALVAR' }}</button>
                        <button class="btn-aviso" @click="editarRoteador">✏️ EDITAR</button>
                        <button class="btn-erro" @click="excluirRoteador">🗑️ EXCLUIR</button>
                        <button class="btn-info" @click="atualizarRoteadores">🔄 ATUALIZAR</button>
                        <button class="btn-secundario" @click="limparFormularioRoteador">🧹 LIMPAR</button>
                    </div>
                </div>

                <div class="rg-card rg-card-filtro">
                    <h3>🔍 Filtro</h3>
                    <div class="rg-grid-filtro">
                        <input v-model="filtros.roteadores" type="text" placeholder="Buscar por IP ou local..." @keyup.enter="atualizarRoteadores" />
                        <div class="rg-botoes-coluna">
                            <button class="btn-primario" @click="atualizarRoteadores">🔍 Aplicar Filtro</button>
                            <button class="btn-secundario" @click="() => { filtros.roteadores = ''; atualizarRoteadores(); }">🗑️ Limpar Filtro</button>
                        </div>
                    </div>
                </div>

                <div class="rg-card rg-card-lista">
                    <h3>📋 Roteadores Cadastrados</h3>
                    <div class="rg-tabela-wrap">
                        <table class="rg-tabela">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>IP</th>
                                    <th>Local</th>
                                    <th>Usuário</th>
                                    <th>Repartição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!carregando.roteadores && roteadores.length === 0">
                                    <td colspan="5">Nenhum roteador cadastrado.</td>
                                </tr>
                                <tr
                                    v-for="item in roteadores"
                                    :key="item.id"
                                    :class="{ selecionada: selecionado.roteador?.id === item.id }"
                                    @click="selecionarRoteador(item)"
                                >
                                    <td>{{ item.id }}</td>
                                    <td>{{ item.ip_roteador }}</td>
                                    <td>{{ item.local_roteador }}</td>
                                    <td>{{ item.usuario }}</td>
                                    <td>{{ item.nome_reparticao || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-show="abaAtiva === 'macs'" class="rg-painel">

                <div class="rg-card rg-card-form">
                    <h3>📝 Cadastrar/Editar MAC Address</h3>
                    <div class="rg-grid-2">
                        <div>
                            <label>🔗 MAC Address</label>
                            <input v-model="formularioMac.mac_address" type="text" placeholder="00:00:00:00:00:00" />
                        </div>
                        <div>
                            <label>👤 Nome do Usuário</label>
                            <input v-model="formularioMac.nome_usuario" type="text" />
                        </div>
                        <div>
                            <label>💼 Função do Usuário</label>
                            <input v-model="formularioMac.funcao_usuario" type="text" />
                        </div>
                        <div>
                            <label>💻 Dispositivo</label>
                            <input v-model="formularioMac.dispositivo" type="text" />
                        </div>
                    </div>
                    <div>
                        <label>📡 Roteador</label>
                        <select v-model="formularioMac.roteador_id">
                            <option v-for="item in combos.roteadores" :key="item.id" :value="String(item.id)">
                                {{ item.id }} - {{ item.ip }} ({{ item.reparticao || '-' }})
                            </option>
                        </select>
                    </div>
                    <div class="rg-acoes">
                        <button class="btn-sucesso" @click="salvarMac">💾 {{ modoEdicaoMac ? 'SALVAR ALTERAÇÕES' : 'SALVAR' }}</button>
                        <button class="btn-aviso" @click="editarMac">✏️ EDITAR</button>
                        <button class="btn-erro" @click="excluirMac">🗑️ EXCLUIR</button>
                        <button class="btn-info" @click="atualizarMacs">🔄 ATUALIZAR</button>
                        <button class="btn-secundario" @click="limparFormularioMac">🧹 LIMPAR</button>
                    </div>
                </div>

                <div class="rg-card rg-card-filtro">
                    <h3>🔍 Filtro</h3>
                    <div class="rg-grid-filtro">
                        <input v-model="filtros.macs" type="text" placeholder="Buscar por MAC ou usuário..." @keyup.enter="atualizarMacs" />
                        <div class="rg-botoes-coluna">
                            <button class="btn-primario" @click="atualizarMacs">🔍 Aplicar Filtro</button>
                            <button class="btn-secundario" @click="() => { filtros.macs = ''; atualizarMacs(); }">🗑️ Limpar Filtro</button>
                        </div>
                    </div>
                </div>

                <div class="rg-card rg-card-lista">
                    <h3>📋 MAC Addresses Cadastrados</h3>
                    <div class="rg-tabela-wrap">
                        <table class="rg-tabela">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>MAC Address</th>
                                    <th>Usuário</th>
                                    <th>Função</th>
                                    <th>Dispositivo</th>
                                    <th>Data</th>
                                    <th>Roteador</th>
                                    <th>Repartição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!carregando.macs && macs.length === 0">
                                    <td colspan="8">Nenhum MAC cadastrado.</td>
                                </tr>
                                <tr
                                    v-for="item in macs"
                                    :key="item.id"
                                    :class="{ selecionada: selecionado.mac?.id === item.id }"
                                    @click="selecionarMac(item)"
                                >
                                    <td>{{ item.id }}</td>
                                    <td>{{ item.mac_address }}</td>
                                    <td>{{ item.nome_usuario }}</td>
                                    <td>{{ item.funcao_usuario || '' }}</td>
                                    <td>{{ item.dispositivo || '' }}</td>
                                    <td>{{ item.data_cadastro }}</td>
                                    <td>{{ item.ip_roteador || '-' }}</td>
                                    <td>{{ item.nome_reparticao || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-show="abaAtiva === 'relatorios'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>📊 Gerar Relatórios</h3>
                    <div class="rg-relatorios-grid">
                        <div class="rg-relatorio-box">
                            <h4>📋 Repartições</h4>
                            <button class="btn-primario" @click="gerarRelatorioReparticoesPdf">PDF</button>
                            <button class="btn-info" @click="gerarRelatorioReparticoesExcel">Excel</button>
                        </div>
                        <div class="rg-relatorio-box">
                            <h4>📡 Roteador Específico</h4>
                            <select v-model="ipRelatorioSelecionado">
                                <option v-for="item in combos.roteadores" :key="item.id" :value="item.ip">
                                    {{ item.ip }} - {{ item.reparticao || '-' }}
                                </option>
                            </select>
                            <button class="btn-primario" @click="gerarRelatorioRoteadorPdf">PDF</button>
                            <button class="btn-info" @click="gerarRelatorioRoteadorExcel">Excel</button>
                        </div>
                        <div class="rg-relatorio-box">
                            <h4>🔗 MAC Addresses</h4>
                            <button class="btn-sucesso" @click="gerarRelatorioMacsPdf">PDF</button>
                            <button class="btn-info" @click="gerarRelatorioMacsExcel">Excel</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
