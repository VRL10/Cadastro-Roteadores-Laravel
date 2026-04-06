<?php

namespace App\Services;

use App\Models\Roteador;
use Illuminate\Database\Eloquent\Collection;

class RoteadorService
{
	public function listar(?string $filtro = null): Collection
	{
		return Roteador::query()
			->with('reparticao:id,nome_reparticao')
			->when($filtro, function ($query, $filtroAplicado) {
				$query->where(function ($subquery) use ($filtroAplicado) {
					$subquery
						->where('ip_roteador', 'like', "%{$filtroAplicado}%")
						->orWhere('local_roteador', 'like', "%{$filtroAplicado}%")
						->orWhere('usuario', 'like', "%{$filtroAplicado}%")
						->orWhereHas('reparticao', function ($queryReparticao) use ($filtroAplicado) {
							$queryReparticao->where('nome_reparticao', 'like', "%{$filtroAplicado}%");
						});
				});
			})
			->orderBy('id')
			->get();
	}

	public function cadastrar(array $dados): Roteador
	{
		return Roteador::create($dados);
	}

	public function atualizar(Roteador $roteador, array $dados): Roteador
	{
		$roteador->update($dados);

		return $roteador->refresh();
	}

	public function excluir(Roteador $roteador): void
	{
		$roteador->delete();
	}

	public function listarParaCombobox(): Collection
	{
		return Roteador::query()
			->with('reparticao:id,nome_reparticao')
			->orderByDesc('id')
			->get();
	}

	public function obterUltimo(): ?Roteador
	{
		return Roteador::query()
			->with('reparticao:id,nome_reparticao')
			->latest('id')
			->first();
	}

	public function buscarPorIp(string $ip): ?Roteador
	{
		return Roteador::query()
			->with('reparticao')
			->where('ip_roteador', $ip)
			->first();
	}
}
