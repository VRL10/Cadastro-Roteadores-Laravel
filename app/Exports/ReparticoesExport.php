<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReparticoesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $reparticoes) {}

    public function collection(): Collection
    {
        return $this->reparticoes->map(fn ($reparticao) => [
            'id' => $reparticao->id,
            'nome_contato' => $reparticao->nome_contato,
            'nome_reparticao' => $reparticao->nome_reparticao,
            'telefone' => $reparticao->telefone,
            'endereco' => $reparticao->endereco,
            'observacoes' => $reparticao->observacoes,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Nome do Contato', 'Reparticao', 'Telefone', 'Endereco', 'Observacoes'];
    }
}
