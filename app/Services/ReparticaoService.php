<?php

namespace App\Services;

use App\Models\Reparticao;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class ReparticaoService
{
	public function __construct(private readonly LocalDataStore $localDataStore) {}

	// Listar as repartições, com opção de filtro por nome do contato, nome da repartição, endereço ou telefone
	public function listar(?string $filtro = null): Collection {
		if ($this->localDataStore->databaseDisponivel()) {
			try {
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
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		$registros = $this->localDataStore->ler('reparticoes');
		$filtroNormalizado = $this->localDataStore->normalizarTexto($filtro);

		$registrosFiltrados = array_values(array_filter($registros, function (array $registro) use ($filtroNormalizado): bool {
			if ($filtroNormalizado === '') {
				return true;
			}

			$texto = $this->localDataStore->normalizarTexto(implode(' ', [
				$registro['nome_contato'] ?? '',
				$registro['nome_reparticao'] ?? '',
				$registro['endereco'] ?? '',
				$registro['telefone'] ?? '',
			]));

			return str_contains($texto, $filtroNormalizado);
		}));

		usort($registrosFiltrados, fn (array $a, array $b): int => ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0)));

		return collect(array_map(static fn (array $registro) => (object) $registro, $registrosFiltrados));
	}

	// Cadastrar uma nova repartição
	public function cadastrar(array $dados): object{
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Reparticao::create($dados);
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		$registros = $this->localDataStore->ler('reparticoes');
		$agora = now()->toDateTimeString();
		$dados['id'] = $this->localDataStore->proximoId('reparticoes');
		$dados['created_at'] = $agora;
		$dados['updated_at'] = $agora;
		$registros[] = $dados;
		$this->localDataStore->salvar('reparticoes', $registros);

		return (object) $dados;
	}

	// Atualizar uma repartição existente
	public function atualizar(object $reparticao, array $dados): object {
		if ($reparticao instanceof Reparticao) {
			$reparticao->update($dados);

			return $reparticao->refresh();
		}

		$registros = $this->localDataStore->ler('reparticoes');
		$id = (int) data_get($reparticao, 'id', 0);

		foreach ($registros as $indice => $registro) {
			if ((int) ($registro['id'] ?? 0) === $id) {
				$dados['id'] = $id;
				$dados['created_at'] = $registro['created_at'] ?? now()->toDateTimeString();
				$dados['updated_at'] = now()->toDateTimeString();
				$registros[$indice] = array_merge($registro, $dados);
				$this->localDataStore->salvar('reparticoes', $registros);

				return (object) $registros[$indice];
			}
		}

		return (object) $dados;
	}

	// Excluir uma repartição
	public function excluir(object $reparticao): void{
		if ($reparticao instanceof Reparticao) {
			$reparticao->delete();
			return;
		}

		$id = (int) data_get($reparticao, 'id', 0);
		$registros = array_values(array_filter($this->localDataStore->ler('reparticoes'), static fn (array $registro): bool => (int) ($registro['id'] ?? 0) !== $id));
		$this->localDataStore->salvar('reparticoes', $registros);
	}

	// Listar as repartições para uso em um combo box, retornando apenas o ID e o nome da repartição
	public function listarParaCombo(): Collection {
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Reparticao::query()
					->select(['id', 'nome_reparticao as nome'])
					->orderBy('nome_reparticao')
					->get();
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		return collect($this->listar()->map(static fn ($reparticao) => (object) [
			'id' => data_get($reparticao, 'id'),
			'nome' => data_get($reparticao, 'nome_reparticao'),
		])->all())->sortBy('nome')->values();
	}

	public function buscarPorId(int $id): object|null
	{
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Reparticao::find($id);
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		foreach ($this->localDataStore->ler('reparticoes') as $registro) {
			if ((int) ($registro['id'] ?? 0) === $id) {
				return (object) $registro;
			}
		}

		return null;
	}

	public function existeNomeReparticao(string $nomeReparticao, ?int $ignorarId = null): bool
	{
		$filtro = $this->localDataStore->normalizarTexto($nomeReparticao);

		return $this->listar()->contains(function ($reparticao) use ($filtro, $ignorarId): bool {
			if ($ignorarId !== null && (int) data_get($reparticao, 'id', 0) === $ignorarId) {
				return false;
			}

			return $this->localDataStore->normalizarTexto((string) data_get($reparticao, 'nome_reparticao')) === $filtro;
		});
	}
}
