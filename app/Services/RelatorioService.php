<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RelatorioService
{
	public function __construct(
		private readonly ReparticaoService $reparticaoService,
		private readonly RoteadorService $roteadorService,
		private readonly MacAddressService $macAddressService,
	) {}

	// Listar todas as repartições, ordenadas por nome
	public function dadosReparticoes(): Collection {
		return $this->reparticaoService->listar()->sortBy('nome_reparticao')->values();
	}
	
	// Lista dados do MacAddress, incluindo informações do roteador e da repartição associada, com ordenação por nome de usuário
	public function dadosMacs(): Collection
	{
		return $this->macAddressService->listar()->sortBy('nome_usuario')->values();
	}

	// Listar os roteadores, incluindo informações da repartição associada, ordenados por IP
	public function dadosRoteadorPorIp(string $ip): object|null
	{
		$roteador = $this->roteadorService->buscarPorIp($ip);

		if (! $roteador) {
			return null;
		}

		$macs = $this->macAddressService->listar((int) data_get($roteador, 'id'))
			->sortBy('nome_usuario')
			->values();

		if ($roteador instanceof \App\Models\Roteador) {
			$roteador->setRelation('enderecosMac', $macs);

			return $roteador;
		}

		$roteador->enderecosMac = $macs;

		return $roteador;
	}
}
