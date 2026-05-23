<?php

namespace App\Services;

use App\Models\Reparticao;
use Illuminate\Database\Eloquent\Collection;

class ReparticaoService
{
	// Listar as repartições, com opção de filtro por nome do contato, nome da repartição, endereço ou telefone
	public function listar(?string $filtro = null): Collection {
		// Retornar as repartições, aplicando o filtro se fornecido, e ordenando por ID
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

	// Cadastrar uma nova repartição
	public function cadastrar(array $dados): Reparticao{
		return Reparticao::create($dados);
	}

	// Atualizar uma repartição existente
	public function atualizar(Reparticao $reparticao, array $dados): Reparticao {
		$reparticao->update($dados);

		return $reparticao->refresh();
	}

	// Excluir uma repartição
	public function excluir(Reparticao $reparticao): void{
		$reparticao->delete();
	}

	// Listar as repartições para uso em um combo box, retornando apenas o ID e o nome da repartição
	public function listarParaCombo(): Collection {
		return Reparticao::query()
			->select(['id', 'nome_reparticao as nome'])
			->orderBy('nome_reparticao')
			->get();
	}
}
