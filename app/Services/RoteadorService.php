<?php

namespace App\Services;

use App\Models\Roteador;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class RoteadorService
{
	// Listar os roteadores, com opção de filtro por IP, local, usuário ou nome da repartição associada
	public function __construct(private readonly LocalDataStore $localDataStore) {}

	public function listar(?string $filtro = null): Collection
	{
		if ($this->localDataStore->databaseDisponivel()) {
			try {
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
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		$roteadores = $this->localDataStore->ler('roteadores');
		$reparticoes = collect($this->localDataStore->ler('reparticoes'))->keyBy(fn (array $registro) => (int) ($registro['id'] ?? 0));
		$filtroNormalizado = $this->localDataStore->normalizarTexto($filtro);

		$roteadoresFiltrados = array_values(array_filter($roteadores, function (array $roteador) use ($filtroNormalizado, $reparticoes): bool {
			if ($filtroNormalizado === '') {
				return true;
			}

			$reparticao = $reparticoes->get((int) ($roteador['reparticao_id'] ?? 0), []);
			$texto = $this->localDataStore->normalizarTexto(implode(' ', [
				$roteador['ip_roteador'] ?? '',
				$roteador['local_roteador'] ?? '',
				$roteador['usuario'] ?? '',
				data_get($reparticao, 'nome_reparticao', ''),
			]));

			return str_contains($texto, $filtroNormalizado);
		}));

		usort($roteadoresFiltrados, fn (array $a, array $b): int => ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0)));

		return collect(array_map(function (array $roteador) use ($reparticoes) {
			$reparticao = $reparticoes->get((int) ($roteador['reparticao_id'] ?? 0), []);

			return (object) array_merge($roteador, [
				'reparticao' => ! empty($reparticao) ? (object) $reparticao : null,
				'nome_reparticao' => data_get($reparticao, 'nome_reparticao'),
			]);
		}, $roteadoresFiltrados));
	}

	// Cadastrar um novo roteador
	public function cadastrar(array $dados): object {
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Roteador::create($dados);
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		$registros = $this->localDataStore->ler('roteadores');
		$agora = now()->toDateTimeString();
		$dados['id'] = $this->localDataStore->proximoId('roteadores');
		$dados['created_at'] = $agora;
		$dados['updated_at'] = $agora;
		$registros[] = $dados;
		$this->localDataStore->salvar('roteadores', $registros);

		return (object) $dados;
	}

	// Atualizar um roteador existente
	public function atualizar(object $roteador, array $dados): object {
		if ($roteador instanceof Roteador) {
			$roteador->update($dados);

			return $roteador->refresh();
		}

		$registros = $this->localDataStore->ler('roteadores');
		$id = (int) data_get($roteador, 'id', 0);

		foreach ($registros as $indice => $registro) {
			if ((int) ($registro['id'] ?? 0) === $id) {
				$dados['id'] = $id;
				$dados['created_at'] = $registro['created_at'] ?? now()->toDateTimeString();
				$dados['updated_at'] = now()->toDateTimeString();
				$registros[$indice] = array_merge($registro, $dados);
				$this->localDataStore->salvar('roteadores', $registros);

				return (object) $registros[$indice];
			}
		}

		return (object) $dados;
	}

	// Excluir um roteador
	public function excluir(object $roteador): void
	{
		if ($roteador instanceof Roteador) {
			$roteador->delete();
			return;
		}

		$id = (int) data_get($roteador, 'id', 0);
		$registros = array_values(array_filter($this->localDataStore->ler('roteadores'), static fn (array $registro): bool => (int) ($registro['id'] ?? 0) !== $id));
		$this->localDataStore->salvar('roteadores', $registros);
	}

	// Listar os roteadores para uso em um combo box, retornando apenas o ID e o nome do roteador
	public function listarParaCombobox(): Collection
	{
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Roteador::query()
					->with('reparticao:id,nome_reparticao')
					->orderByDesc('id')
					->get();
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		return $this->listar()->sortByDesc('id')->values()->map(static fn ($roteador) => (object) [
			'id' => data_get($roteador, 'id'),
			'ip_roteador' => data_get($roteador, 'ip_roteador'),
			'reparticao' => data_get($roteador, 'reparticao.nome_reparticao') ?? data_get($roteador, 'nome_reparticao'),
		]);
	}

	// Obter o último roteador cadastrado, incluindo informações da repartição associada
	public function obterUltimo(): object|null {
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Roteador::query()
					->with('reparticao:id,nome_reparticao')
					->latest('id')
					->first();
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		return $this->listar()->sortByDesc('id')->first();
	}

	// Buscar um roteador por IP, incluindo informações da repartição associada
	public function buscarPorIp(string $ip): object|null {
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Roteador::query()
					->with('reparticao')
					->where('ip_roteador', $ip)
					->first();
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		return $this->listar()->first(fn ($roteador) => data_get($roteador, 'ip_roteador') === $ip);
	}

	public function buscarPorId(int $id): object|null
	{
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return Roteador::find($id);
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		return $this->listar()->first(fn ($roteador) => (int) data_get($roteador, 'id', 0) === $id);
	}

	public function existeIpRoteador(string $ipRoteador, ?int $ignorarId = null): bool
	{
		return $this->listar()->contains(function ($roteador) use ($ipRoteador, $ignorarId): bool {
			if ($ignorarId !== null && (int) data_get($roteador, 'id', 0) === $ignorarId) {
				return false;
			}

			return data_get($roteador, 'ip_roteador') === $ipRoteador;
		});
	}

	public function existeReparticaoId(int $reparticaoId): bool
	{
		return (bool) $this->localDataStore->ler('reparticoes') && collect($this->localDataStore->ler('reparticoes'))->contains(fn (array $reparticao): bool => (int) ($reparticao['id'] ?? 0) === $reparticaoId);
	}
}
