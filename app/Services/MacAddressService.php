<?php

namespace App\Services;

use App\Models\MacAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class MacAddressService
{
	public function listar(?int $roteadorId = null, ?string $filtro = null): Collection
	{
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

	public function cadastrar(array $dados): MacAddress
	{
		if (empty($dados['data_cadastro'])) {
			$dados['data_cadastro'] = Carbon::now()->toDateString();
		}

		return MacAddress::create($dados);
	}

	public function atualizar(MacAddress $macAddress, array $dados): MacAddress
	{
		$macAddress->update($dados);

		return $macAddress->refresh();
	}

	public function excluir(MacAddress $macAddress): void
	{
		$macAddress->delete();
	}
}
