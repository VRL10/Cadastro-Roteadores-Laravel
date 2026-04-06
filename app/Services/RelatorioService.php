<?php

namespace App\Services;

use App\Models\MacAddress;
use App\Models\Reparticao;
use App\Models\Roteador;
use Illuminate\Database\Eloquent\Collection;

class RelatorioService
{
	public function dadosReparticoes(): Collection
	{
		return Reparticao::query()->orderBy('nome_reparticao')->get();
	}

	public function dadosMacs(): Collection
	{
		return MacAddress::query()
			->with('roteador.reparticao:id,nome_reparticao')
			->orderBy('nome_usuario')
			->get();
	}

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
