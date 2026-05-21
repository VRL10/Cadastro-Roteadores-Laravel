<script setup lang="ts">
import type { SistemaCadastroState } from '@/composables/useSistemaCadastro';

const props = defineProps<{
    sistema: SistemaCadastroState;
}>();
</script>

<template>
    <div class="rg-pagina">
        <!-- Cabecalho principal da pagina -->
        <div class="rg-cabecalho">
            <div class="rg-cabecalho-gradiente">
                <h1>&#x1F3E2; Sistema de Cadastro - Repartições e Roteadores</h1>
            </div>
        </div>

        <div class="rg-conteudo">
            <div v-if="props.sistema.alerta.visivel" class="rg-alerta" :class="props.sistema.classeAlerta">
                <strong>{{ props.sistema.alerta.titulo }}</strong>
                <span>{{ props.sistema.alerta.mensagem }}</span>
            </div>

            <nav class="rg-abas" aria-label="Navegação principal">
                <button class="rg-aba" :class="{ ativo: props.sistema.abaAtiva === 'reparticoes' }" @click="props.sistema.trocarAba('reparticoes')">&#x1F3E2; Repartições</button>
                <button class="rg-aba" :class="{ ativo: props.sistema.abaAtiva === 'roteadores' }" @click="props.sistema.trocarAba('roteadores')">&#x1F4E1; Roteadores</button>
                <button class="rg-aba" :class="{ ativo: props.sistema.abaAtiva === 'macs' }" @click="props.sistema.trocarAba('macs')">&#x1F517; MAC Addresses</button>
                <button class="rg-aba" :class="{ ativo: props.sistema.abaAtiva === 'relatorios' }" @click="props.sistema.trocarAba('relatorios')">&#x1F4CA; Relatórios</button>
            </nav>

            <section v-show="props.sistema.abaAtiva === 'reparticoes'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>&#x1F4DD; Cadastrar/Editar Repartição</h3>
                    <div class="rg-grid-2">
                        <div>
                            <label>&#x1F464; Nome do Contato</label>
                            <input v-model="props.sistema.formularioReparticao.nome_contato" type="text" />
                        </div>
                        <div>
                            <label>&#x1F3E2; Nome da Repartição</label>
                            <input v-model="props.sistema.formularioReparticao.nome_reparticao" type="text" />
                        </div>
                        <div>
                            <label>&#x1F4DE; Telefone</label>
                            <input v-model="props.sistema.formularioReparticao.telefone" type="text" placeholder="(11) 99999-9999" @input="props.sistema.aoDigitarTelefone" />
                        </div>
                        <div>
                            <label>&#x1F4CD; Endereço</label>
                            <input v-model="props.sistema.formularioReparticao.endereco" type="text" />
                        </div>
                    </div>
                    <div>
                        <label>&#x1F4DD; Observações</label>
                        <textarea
                            ref="props.sistema.campoObservacoesRef"
                            v-model="props.sistema.formularioReparticao.observacoes"
                            class="rg-textarea-observacoes"
                            rows="2"
                            @input="props.sistema.ajustarAlturaObservacoes"
                        ></textarea>
                    </div>
                    <div class="rg-acoes">
                        <button class="btn-sucesso" @click="props.sistema.salvarReparticao">&#x1F4BE; {{ props.sistema.modoEdicaoReparticao ? 'SALVAR ALTERAÇÕES' : 'SALVAR' }}</button>
                        <button class="btn-aviso" @click="props.sistema.editarReparticao">&#x270F;&#xFE0F; EDITAR</button>
                        <button class="btn-erro" @click="props.sistema.excluirReparticao">&#x1F5D1;&#xFE0F; EXCLUIR</button>
                        <button class="btn-info" @click="props.sistema.atualizarReparticoes">&#x1F504; ATUALIZAR</button>
                        <button class="btn-secundario" @click="props.sistema.limparFormularioReparticao">&#x1F9F9; LIMPAR</button>
                    </div>
                </div>

                <div class="rg-card rg-card-filtro">
                    <h3>&#x1F50D; Filtro</h3>
                    <div class="rg-grid-filtro">
                        <input v-model="props.sistema.filtros.reparticoes" type="text" placeholder="Buscar por contato, repartição, telefone..." @keyup.enter="props.sistema.aplicarFiltroReparticoes" />
                        <div class="rg-botoes-coluna">
                            <button class="btn-primario" @click="props.sistema.aplicarFiltroReparticoes">&#x1F50D; Aplicar Filtro</button>
                            <button class="btn-secundario" @click="() => { props.sistema.filtros.reparticoes = ''; props.sistema.atualizarReparticoes(); }">&#x1F5D1;&#xFE0F; Limpar Filtro</button>
                        </div>
                    </div>
                </div>

                <div class="rg-card rg-card-lista">
                    <h3>&#x1F4CB; Repartições Cadastradas</h3>
                    <div class="rg-tabela-wrap">
                        <table class="rg-tabela rg-tabela-reparticoes">
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
                                <tr v-if="!props.sistema.carregando.reparticoes && props.sistema.reparticoesFiltradas.length === 0">
                                    <td colspan="6">Nenhuma repartição cadastrada.</td>
                                </tr>
                                <tr
                                    v-for="(item, index) in props.sistema.reparticoesFiltradas"
                                    :key="item?.id ?? index"
                                    :class="{ selecionada: props.sistema.selecionado.reparticao?.id === item?.id }"
                                    @click="item && props.sistema.selecionarReparticao(item)"
                                >
                                    <td>{{ item?.id ?? '' }}</td>
                                    <td>{{ item?.nome_contato ?? '' }}</td>
                                    <td>{{ item?.nome_reparticao ?? '' }}</td>
                                    <td>{{ item?.telefone ?? '' }}</td>
                                    <td>{{ item?.endereco ?? '' }}</td>
                                    <td>{{ item?.observacoes || '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-show="props.sistema.abaAtiva === 'roteadores'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>&#x1F4DD; Cadastrar/Editar Roteador</h3>
                    <div class="rg-grid-2">
                        <div>
                            <label>&#x1F310; IP do Roteador</label>
                            <input v-model="props.sistema.formularioRoteador.ip_roteador" type="text" placeholder="192.168.1.1" />
                        </div>
                        <div>
                            <label>&#x1F4CD; Local do Roteador</label>
                            <input v-model="props.sistema.formularioRoteador.local_roteador" type="text" />
                        </div>
                        <div>
                            <label>&#x1F464; Usuário</label>
                            <input v-model="props.sistema.formularioRoteador.usuario" type="text" />
                        </div>
                        <div>
                            <label>&#x1F511; Senha</label>
                            <input v-model="props.sistema.formularioRoteador.senha" :type="props.sistema.exibirSenhaRoteador ? 'text' : 'password'" />
                            <button type="button" class="rg-botao-senha" @click="props.sistema.exibirSenhaRoteador = !props.sistema.exibirSenhaRoteador">
                                {{ props.sistema.exibirSenhaRoteador ? 'Ocultar senha' : 'Ver senha' }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label>&#x1F3E2; Repartição</label>
                        <select v-model="props.sistema.formularioRoteador.reparticao_id">
                            <option v-for="(item, index) in props.sistema.combos.reparticoes" :key="item?.id ?? index" :value="String(item?.id ?? '')">
                                {{ item?.id ?? '' }} - {{ item?.nome ?? '' }}
                            </option>
                        </select>
                    </div>
                    <div class="rg-acoes">
                        <button class="btn-sucesso" @click="props.sistema.salvarRoteador">&#x1F4BE; {{ props.sistema.modoEdicaoRoteador ? 'SALVAR ALTERAÇÕES' : 'SALVAR' }}</button>
                        <button class="btn-aviso" @click="props.sistema.editarRoteador">&#x270F;&#xFE0F; EDITAR</button>
                        <button class="btn-erro" @click="props.sistema.excluirRoteador">&#x1F5D1;&#xFE0F; EXCLUIR</button>
                        <button class="btn-info" @click="props.sistema.atualizarRoteadores">&#x1F504; ATUALIZAR</button>
                        <button class="btn-secundario" @click="props.sistema.limparFormularioRoteador">&#x1F9F9; LIMPAR</button>
                    </div>
                </div>

                <div class="rg-card rg-card-filtro">
                    <h3>&#x1F50D; Filtro</h3>
                    <div class="rg-grid-filtro">
                        <input v-model="props.sistema.filtros.roteadores" type="text" placeholder="Buscar por IP ou local..." @keyup.enter="props.sistema.aplicarFiltroRoteadores" />
                        <div class="rg-botoes-coluna">
                            <button class="btn-primario" @click="props.sistema.aplicarFiltroRoteadores">&#x1F50D; Aplicar Filtro</button>
                            <button class="btn-secundario" @click="() => { props.sistema.filtros.roteadores = ''; props.sistema.atualizarRoteadores(); }">&#x1F5D1;&#xFE0F; Limpar Filtro</button>
                        </div>
                    </div>
                </div>

                <div class="rg-card rg-card-lista">
                    <h3>&#x1F4CB; Roteadores Cadastrados</h3>
                    <div class="rg-tabela-wrap">
                        <table class="rg-tabela rg-tabela-roteadores">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>IP</th>
                                    <th>Local</th>
                                    <th>Usuário</th>
                                    <th>Repartição</th>
                                    <th>Senha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!props.sistema.carregando.roteadores && props.sistema.roteadoresFiltrados.length === 0">
                                    <td colspan="6">Nenhum roteador cadastrado.</td>
                                </tr>
                                <tr
                                    v-for="(item, index) in props.sistema.roteadoresFiltrados"
                                    :key="item?.id ?? index"
                                    :class="{ selecionada: props.sistema.selecionado.roteador?.id === item?.id }"
                                    @click="item && props.sistema.selecionarRoteador(item)"
                                >
                                    <td>{{ item?.id ?? '' }}</td>
                                    <td>{{ item?.ip_roteador ?? '' }}</td>
                                    <td>{{ item?.local_roteador ?? '' }}</td>
                                    <td>{{ item?.usuario ?? '' }}</td>
                                    <td>{{ item?.nome_reparticao || '-' }}</td>
                                    <td>{{ item?.senha ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-show="props.sistema.abaAtiva === 'macs'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>&#x1F4DD; Cadastrar/Editar MAC Address</h3>
                    <div class="rg-grid-2">
                        <div>
                            <label>&#x1F517; MAC Address</label>
                            <input v-model="props.sistema.formularioMac.mac_address" type="text" placeholder="00:00:00:00:00:00" @input="props.sistema.aoDigitarMac" />
                        </div>
                        <div>
                            <label>&#x1F464; Nome do Usuário</label>
                            <input v-model="props.sistema.formularioMac.nome_usuario" type="text" />
                        </div>
                        <div>
                            <label>&#x1F4BC; Função do Usuário</label>
                            <input v-model="props.sistema.formularioMac.funcao_usuario" type="text" />
                        </div>
                        <div>
                            <label>&#x1F4BB; Dispositivo</label>
                            <input v-model="props.sistema.formularioMac.dispositivo" type="text" />
                        </div>
                    </div>
                    <div>
                        <label>&#x1F4E1; Roteador</label>
                        <select v-model="props.sistema.formularioMac.roteador_id">
                            <option v-for="(item, index) in props.sistema.combos.roteadores" :key="item?.id ?? index" :value="String(item?.id ?? '')">
                                {{ item?.id ?? '' }} - {{ item?.ip ?? '' }} ({{ item?.reparticao || '-' }})
                            </option>
                        </select>
                    </div>
                    <div class="rg-acoes">
                        <button class="btn-sucesso" @click="props.sistema.salvarMac">&#x1F4BE; {{ props.sistema.modoEdicaoMac ? 'SALVAR ALTERAÇÕES' : 'SALVAR' }}</button>
                        <button class="btn-aviso" @click="props.sistema.editarMac">&#x270F;&#xFE0F; EDITAR</button>
                        <button class="btn-erro" @click="props.sistema.excluirMac">&#x1F5D1;&#xFE0F; EXCLUIR</button>
                        <button class="btn-info" @click="props.sistema.atualizarMacs">&#x1F504; ATUALIZAR</button>
                        <button class="btn-secundario" @click="props.sistema.limparFormularioMac">&#x1F9F9; LIMPAR</button>
                    </div>
                </div>

                <div class="rg-card rg-card-filtro">
                    <h3>&#x1F50D; Filtro</h3>
                    <div class="rg-grid-filtro">
                        <input v-model="props.sistema.filtros.macs" type="text" placeholder="Buscar por MAC ou usuário..." @keyup.enter="props.sistema.aplicarFiltroMacs" />
                        <div class="rg-botoes-coluna">
                            <button class="btn-primario" @click="props.sistema.aplicarFiltroMacs">&#x1F50D; Aplicar Filtro</button>
                            <button class="btn-secundario" @click="() => { props.sistema.filtros.macs = ''; props.sistema.atualizarMacs(); }">&#x1F5D1;&#xFE0F; Limpar Filtro</button>
                        </div>
                    </div>
                </div>

                <div class="rg-card rg-card-lista">
                    <h3>&#x1F4CB; MAC Addresses Cadastrados</h3>
                    <div class="rg-tabela-wrap">
                        <table class="rg-tabela rg-tabela-macs">
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
                                <tr v-if="!props.sistema.carregando.macs && props.sistema.macsFiltrados.length === 0">
                                    <td colspan="8">Nenhum MAC cadastrado.</td>
                                </tr>
                                <tr
                                    v-for="(item, index) in props.sistema.macsFiltrados"
                                    :key="item?.id ?? index"
                                    :class="{ selecionada: props.sistema.selecionado.mac?.id === item?.id }"
                                    @click="item && props.sistema.selecionarMac(item)"
                                >
                                    <td>{{ item?.id ?? '' }}</td>
                                    <td>{{ item?.mac_address ?? '' }}</td>
                                    <td>{{ item?.nome_usuario ?? '' }}</td>
                                    <td>{{ item?.funcao_usuario || '' }}</td>
                                    <td>{{ item?.dispositivo || '' }}</td>
                                    <td>{{ item?.data_cadastro ?? '' }}</td>
                                    <td>{{ item?.ip_roteador || '-' }}</td>
                                    <td>{{ item?.nome_reparticao || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-show="props.sistema.abaAtiva === 'relatorios'" class="rg-painel">
                <div class="rg-card rg-card-form">
                    <h3>&#x1F4CA; Gerar Relatórios</h3>
                    <div class="rg-relatorios-grid">
                        <div class="rg-relatorio-box">
                            <h4>&#x1F4CB; Repartições</h4>
                            <button class="btn-primario" @click="props.sistema.gerarRelatorioReparticoesPdf">PDF</button>
                            <button class="btn-info" @click="props.sistema.gerarRelatorioReparticoesExcel">Excel</button>
                        </div>
                        <div class="rg-relatorio-box">
                            <h4>&#x1F4E1; Roteador Específico</h4>
                            <select v-model="props.sistema.ipRelatorioSelecionado">
                                <option v-for="(item, index) in props.sistema.combos.roteadores" :key="item?.id ?? index" :value="item?.ip ?? ''">
                                    {{ item?.ip ?? '' }} - {{ item?.reparticao || '-' }}
                                </option>
                            </select>
                            <button class="btn-primario" @click="props.sistema.gerarRelatorioRoteadorPdf">PDF</button>
                            <button class="btn-info" @click="props.sistema.gerarRelatorioRoteadorExcel">Excel</button>
                        </div>
                        <div class="rg-relatorio-box">
                            <h4>&#x1F517; MAC Addresses</h4>
                            <button class="btn-sucesso" @click="props.sistema.gerarRelatorioMacsPdf">PDF</button>
                            <button class="btn-info" @click="props.sistema.gerarRelatorioMacsExcel">Excel</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
