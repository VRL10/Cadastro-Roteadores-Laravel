<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio do Roteador</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2 { margin: 0 0 8px; color: #0f172a; }
        .bloco { margin-bottom: 16px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; }
        .azul { background: #eff6ff; border-color: #bfdbfe; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #dbeafe; }
    </style>
</head>
<body>
    <div class="bloco azul">
        <h1>Relatorio do Roteador {{ $roteador->ip_roteador }}</h1>
        <div>Gerado em: {{ $geradoEm->format('d/m/Y H:i') }}</div>
    </div>

    <div class="bloco">
        <h2>Informacoes da Reparticao</h2>
        <div><strong>Reparticao:</strong> {{ $roteador->reparticao?->nome_reparticao ?: '-' }}</div>
        <div><strong>Contato:</strong> {{ $roteador->reparticao?->nome_contato ?: '-' }}</div>
        <div><strong>Telefone:</strong> {{ $roteador->reparticao?->telefone ?: '-' }}</div>
        <div><strong>Endereco:</strong> {{ $roteador->reparticao?->endereco ?: '-' }}</div>
    </div>

    <div class="bloco">
        <h2>Dados do Roteador</h2>
        <div><strong>IP:</strong> {{ $roteador->ip_roteador }}</div>
        <div><strong>Local:</strong> {{ $roteador->local_roteador }}</div>
        <div><strong>Usuario:</strong> {{ $roteador->usuario }}</div>
        <div><strong>Senha:</strong> {{ $roteador->senha }}</div>
    </div>

    <div class="bloco">
        <h2>MAC Addresses ({{ $roteador->enderecosMac->count() }})</h2>
        <table>
            <thead>
                <tr>
                    <th>MAC Address</th>
                    <th>Usuario</th>
                    <th>Funcao</th>
                    <th>Dispositivo</th>
                    <th>Data Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roteador->enderecosMac as $mac)
                    <tr>
                        <td>{{ $mac->mac_address }}</td>
                        <td>{{ $mac->nome_usuario }}</td>
                        <td>{{ $mac->funcao_usuario ?: '-' }}</td>
                        <td>{{ $mac->dispositivo ?: '-' }}</td>
                        <td>{{ $mac->data_cadastro?->format('d/m/Y') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Nenhum MAC cadastrado para este roteador.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
