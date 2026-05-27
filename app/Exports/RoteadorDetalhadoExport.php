<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoteadorDetalhadoExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly object $roteador) {}

    public function collection(): Collection
    {
        return $this->roteador->enderecosMac->map(fn ($mac) => [
            'ip_roteador' => $this->roteador->ip_roteador,
            'local_roteador' => $this->roteador->local_roteador,
            'usuario_roteador' => $this->roteador->usuario,
            'reparticao' => $this->roteador->reparticao?->nome_reparticao,
            'mac_address' => $mac->mac_address,
            'nome_usuario' => $mac->nome_usuario,
            'funcao_usuario' => $mac->funcao_usuario,
            'dispositivo' => $mac->dispositivo,
            'data_cadastro' => $mac->data_cadastro?->format('Y-m-d'),
        ]);
    }

    public function headings(): array
    {
        return [
            'IP do Roteador',
            'Local do Roteador',
            'Usuario do Roteador',
            'Reparticao',
            'MAC Address',
            'Nome do Usuario',
            'Funcao do Usuario',
            'Dispositivo',
            'Data Cadastro',
        ];
    }
}
