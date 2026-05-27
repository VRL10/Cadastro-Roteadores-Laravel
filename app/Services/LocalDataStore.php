<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LocalDataStore
{
    private ?bool $databaseDisponivel = null;

    public function databaseDisponivel(): bool
    {
        if ($this->databaseDisponivel !== null) {
            return $this->databaseDisponivel;
        }

        try {
            DB::connection()->getPdo();
            $this->databaseDisponivel = true;
        } catch (\Throwable) {
            $this->databaseDisponivel = false;
        }

        return $this->databaseDisponivel;
    }

    public function ler(string $nomeArquivo): array
    {
        $caminho = $this->caminho($nomeArquivo);

        if (! is_file($caminho)) {
            return [];
        }

        $conteudo = file_get_contents($caminho);
        if ($conteudo === false || trim($conteudo) === '') {
            return [];
        }

        $dados = json_decode($conteudo, true);

        return is_array($dados) ? $dados : [];
    }

    public function salvar(string $nomeArquivo, array $registros): void
    {
        $this->garantirDiretorio();

        file_put_contents(
            $this->caminho($nomeArquivo),
            json_encode(array_values($registros), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX,
        );
    }

    public function proximoId(string $nomeArquivo): int
    {
        $ids = array_map(
            static fn (array $registro): int => (int) ($registro['id'] ?? 0),
            $this->ler($nomeArquivo),
        );

        return $ids !== [] ? (max($ids) + 1) : 1;
    }

    public function normalizarTexto(?string $valor): string
    {
        $texto = trim((string) $valor);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        if ($ascii === false) {
            $ascii = $texto;
        }

        return strtolower(preg_replace('/\s+/', ' ', $ascii) ?? $ascii);
    }

    private function caminho(string $nomeArquivo): string
    {
        return storage_path('app/local-data/'.$nomeArquivo.'.json');
    }

    private function garantirDiretorio(): void
    {
        $diretorio = storage_path('app/local-data');

        if (! is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
    }
}
