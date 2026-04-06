<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MacsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $macs) {}

    public function collection(): Collection
    {
        return $this->macs->map(fn ($mac) => [
            'mac_address' => $mac->mac_address,
            'nome_usuario' => $mac->nome_usuario,
            'funcao_usuario' => $mac->funcao_usuario,
            'dispositivo' => $mac->dispositivo,
            'data_cadastro' => $mac->data_cadastro?->format('Y-m-d'),
            'ip_roteador' => $mac->roteador?->ip_roteador,
            'nome_reparticao' => $mac->roteador?->reparticao?->nome_reparticao,
        ]);
    }

    public function headings(): array
    {
        return ['MAC Address', 'Nome do Usuario', 'Funcao', 'Dispositivo', 'Data Cadastro', 'IP do Roteador', 'Reparticao'];
    }
}
