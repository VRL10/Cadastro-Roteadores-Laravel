<?php

namespace App\Services;

use App\Models\MacAddress;
use App\Models\Reparticao;
use App\Models\Roteador;
use Illuminate\Database\Eloquent\Collection;

class RelatorioService
{
	// Listar todas as repartições, ordenadas por nome
	public function dadosReparticoes(): Collection {
		return Reparticao::query()->orderBy('nome_reparticao')->get();
	}
	
	// Lista dados do MacAddress, incluindo informações do roteador e da repartição associada, com ordenação por nome de usuário
	public function dadosMacs(): Collection
	{
		return MacAddress::query()
			->with('roteador.reparticao:id,nome_reparticao')
			->orderBy('nome_usuario')
			->get();
	}

	// Listar os roteadores, incluindo informações da repartição associada, ordenados por IP
	public function dadosRoteadorPorIp(string $ip): ?Roteador
	{
		return Roteador::query()
			->with([
				'reparticao',
				'enderecosMac' => fn ($query) => $query->orderBy('nome_usuario'),
			])
			->where('ip_roteador', $ip)
			->first();
	}
}
