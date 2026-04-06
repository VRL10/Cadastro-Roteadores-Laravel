<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de MAC Addresses</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { margin: 0 0 8px; color: #0f172a; }
        .cabecalho { margin-bottom: 16px; padding: 12px; background: #ecfdf5; border: 1px solid #86efac; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #dcfce7; }
    </style>
</head>
<body>
    <div class="cabecalho">
        <h1>Relatorio de MAC Addresses</h1>
        <div>Gerado em: {{ $geradoEm->format('d/m/Y H:i') }}</div>
        <div>Total de registros: {{ $macs->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>MAC Address</th>
                <th>Usuario</th>
                <th>Funcao</th>
                <th>Dispositivo</th>
                <th>Data</th>
                <th>Roteador</th>
                <th>Reparticao</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($macs as $mac)
                <tr>
                    <td>{{ $mac->mac_address }}</td>
                    <td>{{ $mac->nome_usuario }}</td>
                    <td>{{ $mac->funcao_usuario ?: '-' }}</td>
                    <td>{{ $mac->dispositivo ?: '-' }}</td>
                    <td>{{ $mac->data_cadastro?->format('d/m/Y') ?: '-' }}</td>
                    <td>{{ $mac->roteador?->ip_roteador ?: '-' }}</td>
                    <td>{{ $mac->roteador?->reparticao?->nome_reparticao ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
