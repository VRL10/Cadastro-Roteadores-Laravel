<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio de Reparticoes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0 0 8px; color: #0f172a; }
        .cabecalho { margin-bottom: 16px; padding: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #dbeafe; }
    </style>
</head>
<body>
    <div class="cabecalho">
        <h1>Relatorio de Reparticoes</h1>
        <div>Gerado em: {{ $geradoEm->format('d/m/Y H:i') }}</div>
        <div>Total de reparticoes: {{ $reparticoes->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Contato</th>
                <th>Reparticao</th>
                <th>Telefone</th>
                <th>Endereco</th>
                <th>Observacoes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reparticoes as $reparticao)
                <tr>
                    <td>{{ $reparticao->id }}</td>
                    <td>{{ $reparticao->nome_contato }}</td>
                    <td>{{ $reparticao->nome_reparticao }}</td>
                    <td>{{ $reparticao->telefone }}</td>
                    <td>{{ $reparticao->endereco }}</td>
                    <td>{{ $reparticao->observacoes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
