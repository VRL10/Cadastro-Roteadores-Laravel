<?php

namespace App\Services;

use App\Models\MacAddress;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class MacAddressService
{	
	public function __construct(private readonly LocalDataStore $localDataStore) {}

	// Listar os endereços MAC, com opções de filtro por roteador e por termo de busca
	public function listar(?int $roteadorId = null, ?string $filtro = null): Collection {
		if ($this->localDataStore->databaseDisponivel()) {
			try {
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
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		$macs = $this->localDataStore->ler('mac_addresses');
		$roteadores = collect($this->localDataStore->ler('roteadores'))->keyBy(fn (array $registro) => (int) ($registro['id'] ?? 0));
		$reparticoes = collect($this->localDataStore->ler('reparticoes'))->keyBy(fn (array $registro) => (int) ($registro['id'] ?? 0));
		$filtroNormalizado = $this->localDataStore->normalizarTexto($filtro);

		$macsFiltrados = array_values(array_filter($macs, function (array $mac) use ($roteadorId, $filtroNormalizado, $roteadores, $reparticoes): bool {
			if ($roteadorId !== null && (int) ($mac['roteador_id'] ?? 0) !== $roteadorId) {
				return false;
			}

			if ($filtroNormalizado === '') {
				return true;
			}

			$roteador = $roteadores->get((int) ($mac['roteador_id'] ?? 0), []);
			$reparticao = $reparticoes->get((int) data_get($roteador, 'reparticao_id', 0), []);
			$texto = $this->localDataStore->normalizarTexto(implode(' ', [
				$mac['mac_address'] ?? '',
				$mac['nome_usuario'] ?? '',
				$mac['funcao_usuario'] ?? '',
				$mac['dispositivo'] ?? '',
				data_get($roteador, 'ip_roteador', ''),
				data_get($reparticao, 'nome_reparticao', ''),
			]));

			return str_contains($texto, $filtroNormalizado);
		}));

		usort($macsFiltrados, fn (array $a, array $b): int => ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0)));

		return collect(array_map(function (array $mac) use ($roteadores, $reparticoes) {
			$roteador = $roteadores->get((int) ($mac['roteador_id'] ?? 0), []);
			$reparticao = $reparticoes->get((int) data_get($roteador, 'reparticao_id', 0), []);
			$dataCadastro = data_get($mac, 'data_cadastro');

			return (object) array_merge($mac, [
				'data_cadastro' => $dataCadastro ? Carbon::parse($dataCadastro) : null,
				'roteador' => ! empty($roteador) ? (object) array_merge((array) $roteador, [
					'reparticao' => ! empty($reparticao) ? (object) $reparticao : null,
				]) : null,
				'ip_roteador' => data_get($roteador, 'ip_roteador'),
				'nome_reparticao' => data_get($reparticao, 'nome_reparticao'),
			]);
		}, $macsFiltrados));
	}

	// Cadastrar um novo endereço MAC
	public function cadastrar(array $dados): object
	{
		// Se a data de cadastro não for fornecida, usar a data atual
		if (empty($dados['data_cadastro'])) {
			$dados['data_cadastro'] = Carbon::now()->toDateString();
		}

		// Criar o novo endereço MAC usando os dados fornecidos
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return MacAddress::create($dados);
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		$registros = $this->localDataStore->ler('mac_addresses');
		$agora = now()->toDateTimeString();
		$dados['id'] = $this->localDataStore->proximoId('mac_addresses');
		$dados['created_at'] = $agora;
		$dados['updated_at'] = $agora;
		$registros[] = $dados;
		$this->localDataStore->salvar('mac_addresses', $registros);

		$dados['data_cadastro'] = Carbon::parse($dados['data_cadastro']);

		return (object) $dados;
	}

	// Atualizar um endereço MAC existente
	public function atualizar(object $macAddress, array $dados): object {
		// Se a data de cadastro for fornecida, garantir que esteja no formato correto
		if ($macAddress instanceof MacAddress) {
			$macAddress->update($dados);

			return $macAddress->refresh();
		}

		$registros = $this->localDataStore->ler('mac_addresses');
		$id = (int) data_get($macAddress, 'id', 0);

		foreach ($registros as $indice => $registro) {
			if ((int) ($registro['id'] ?? 0) === $id) {
				$dados['id'] = $id;
				$dados['created_at'] = $registro['created_at'] ?? now()->toDateTimeString();
				$dados['updated_at'] = now()->toDateTimeString();
				$registros[$indice] = array_merge($registro, $dados);
				$this->localDataStore->salvar('mac_addresses', $registros);
				$registros[$indice]['data_cadastro'] = Carbon::parse((string) ($registros[$indice]['data_cadastro'] ?? $dados['data_cadastro']));

				return (object) $registros[$indice];
			}
		}

		return (object) $dados;
	}

	// Excluir um endereço MAC
	public function excluir(object $macAddress): void {
		if ($macAddress instanceof MacAddress) {
			$macAddress->delete();
			return;
		}

		$id = (int) data_get($macAddress, 'id', 0);
		$registros = array_values(array_filter($this->localDataStore->ler('mac_addresses'), static fn (array $registro): bool => (int) ($registro['id'] ?? 0) !== $id));
		$this->localDataStore->salvar('mac_addresses', $registros);
	}

	public function buscarPorId(int $id): object|null
	{
		if ($this->localDataStore->databaseDisponivel()) {
			try {
				return MacAddress::find($id);
			} catch (QueryException) {
				// Usa o fallback local abaixo quando o banco não responde.
			}
		}

		return $this->listar()->first(fn ($mac) => (int) data_get($mac, 'id', 0) === $id);
	}

	public function existeMacAddress(string $macAddress, ?int $ignorarId = null): bool
	{
		return $this->listar()->contains(function ($mac) use ($macAddress, $ignorarId): bool {
			if ($ignorarId !== null && (int) data_get($mac, 'id', 0) === $ignorarId) {
				return false;
			}

			return data_get($mac, 'mac_address') === $macAddress;
		});
	}

	public function existeRoteadorId(int $roteadorId): bool
	{
		return collect($this->localDataStore->ler('roteadores'))->contains(fn (array $roteador): bool => (int) ($roteador['id'] ?? 0) === $roteadorId);
	}
}
