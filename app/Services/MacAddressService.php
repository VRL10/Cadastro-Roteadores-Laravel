<?php

namespace App\Services;

use App\Models\MacAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class MacAddressService
{	
	// Listar os endereços MAC, com opções de filtro por roteador e por termo de busca
	public function listar(?int $roteadorId = null, ?string $filtro = null): Collection {
		return MacAddress::query()
			->with('roteador.reparticao:id,nome_reparticao')
			->when($roteadorId, function ($query, $idRoteador) {
				$query->where('roteador_id', $idRoteador);
			})
			->when($filtro, function ($query, $filtroAplicado) {
				$query->where(function ($subquery) use ($filtroAplicado) {
					$subquery
						->where('mac_address', 'like', "%{$filtroAplicado}%")
						->orWhere('nome_usuario', 'like', "%{$filtroAplicado}%")
						->orWhere('funcao_usuario', 'like', "%{$filtroAplicado}%")
						->orWhere('dispositivo', 'like', "%{$filtroAplicado}%")
						->orWhereHas('roteador', function ($queryRoteador) use ($filtroAplicado) {
							$queryRoteador
								->where('ip_roteador', 'like', "%{$filtroAplicado}%")
								->orWhereHas('reparticao', function ($queryReparticao) use ($filtroAplicado) {
									$queryReparticao->where('nome_reparticao', 'like', "%{$filtroAplicado}%");
								});
						});
				});
			})
			->orderBy('id')
			->get();
	}

	// Cadastrar um novo endereço MAC
	public function cadastrar(array $dados): MacAddress
	{
		// Se a data de cadastro não for fornecida, usar a data atual
		if (empty($dados['data_cadastro'])) {
			$dados['data_cadastro'] = Carbon::now()->toDateString();
		}

		// Criar o novo endereço MAC usando os dados fornecidos
		return MacAddress::create($dados);
	}

	// Atualizar um endereço MAC existente
	public function atualizar(MacAddress $macAddress, array $dados): MacAddress {
		// Se a data de cadastro for fornecida, garantir que esteja no formato correto
		$macAddress->update($dados);

		return $macAddress->refresh();
	}

	// Excluir um endereço MAC
	public function excluir(MacAddress $macAddress): void {
		$macAddress->delete();
	}
}
