<?php

namespace App\Services;

use App\Models\Roteador;
use Illuminate\Database\Eloquent\Collection;

class RoteadorService
{
	// Listar os roteadores, com opção de filtro por IP, local, usuário ou nome da repartição associada
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

	// Cadastrar um novo roteador
	public function cadastrar(array $dados): Roteador {
		return Roteador::create($dados);
	}

	// Atualizar um roteador existente
	public function atualizar(Roteador $roteador, array $dados): Roteador {
		$roteador->update($dados);

		return $roteador->refresh();
	}

	// Excluir um roteador
	public function excluir(Roteador $roteador): void
	{
		$roteador->delete();
	}

	// Listar os roteadores para uso em um combo box, retornando apenas o ID e o nome do roteador
	public function listarParaCombobox(): Collection
	{
		return Roteador::query()
			->with('reparticao:id,nome_reparticao')
			->orderByDesc('id')
			->get();
	}

	// Obter o último roteador cadastrado, incluindo informações da repartição associada
	public function obterUltimo(): ?Roteador {
		return Roteador::query()
			->with('reparticao:id,nome_reparticao')
			->latest('id')
			->first();
	}

	// Buscar um roteador por IP, incluindo informações da repartição associada
	public function buscarPorIp(string $ip): ?Roteador {
		return Roteador::query()
			->with('reparticao')
			->where('ip_roteador', $ip)
			->first();
	}
}
