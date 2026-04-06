<?php

namespace App\Services;

use App\Models\Reparticao;
use Illuminate\Database\Eloquent\Collection;

class ReparticaoService
{
	public function listar(?string $filtro = null): Collection
	{
		return Reparticao::query()
			->when($filtro, function ($query, $filtroAplicado) {
				$query->where(function ($subquery) use ($filtroAplicado) {
					$subquery
						->where('nome_contato', 'like', "%{$filtroAplicado}%")
						->orWhere('nome_reparticao', 'like', "%{$filtroAplicado}%")
						->orWhere('endereco', 'like', "%{$filtroAplicado}%")
						->orWhere('telefone', 'like', "%{$filtroAplicado}%");
				});
			})
			->orderBy('id')
			->get();
	}

	public function cadastrar(array $dados): Reparticao
	{
		return Reparticao::create($dados);
	}

	public function atualizar(Reparticao $reparticao, array $dados): Reparticao
	{
		$reparticao->update($dados);

		return $reparticao->refresh();
	}

	public function excluir(Reparticao $reparticao): void
	{
		$reparticao->delete();
	}

	public function listarParaCombo(): Collection
	{
		return Reparticao::query()
			->select(['id', 'nome_reparticao as nome'])
			->orderBy('nome_reparticao')
			->get();
	}
}
