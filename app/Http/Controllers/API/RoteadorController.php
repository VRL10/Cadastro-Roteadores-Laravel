<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ReparticaoService;
use App\Services\RoteadorService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoteadorController extends Controller
{
	// Injeção de dependência do serviço de roteador
	public function __construct(
		private readonly RoteadorService $roteadorService,
		private readonly ReparticaoService $reparticaoService,
	) {}

	// Listar os roteadores, com opção de filtro por texto
	public function index(Request $request): JsonResponse {
		// Obter o filtro de texto da query string, se fornecido
		$filtro = trim((string) $request->query('filtro', ''));

		// Listar os roteadores usando o serviço, aplicando o filtro se fornecido
		$roteadores = $this->roteadorService->listar($filtro !== '' ? $filtro : null);

		// Retornar os roteadores em formato JSON, incluindo informações da repartição relacionada
		return response()->json($roteadores->map(fn ($roteador) => [
			'id' => data_get($roteador, 'id'),
			'ip_roteador' => data_get($roteador, 'ip_roteador'),
			'local_roteador' => data_get($roteador, 'local_roteador'),
			'usuario' => data_get($roteador, 'usuario'),
			'senha' => data_get($roteador, 'senha'),
			'reparticao_id' => data_get($roteador, 'reparticao_id'),
			'nome_reparticao' => data_get($roteador, 'reparticao.nome_reparticao') ?? data_get($roteador, 'nome_reparticao'),
		]));
	}

	// Cadastrar um novo roteador
	public function store(Request $request): JsonResponse {
		// Validar os dados de entrada para criar um novo roteador
		$dados = $request->validate([
			'ip_roteador' => ['required', 'string', 'max:50'],
			'local_roteador' => ['required', 'string', 'max:255'],
			'usuario' => ['required', 'string', 'max:255'],
			'senha' => ['required', 'string', 'max:255'],
			'reparticao_id' => ['required', 'integer'],
		]);

		if ($this->roteadorService->existeIpRoteador($dados['ip_roteador'])) {
			throw ValidationException::withMessages([
				'ip_roteador' => 'Este IP de roteador ja esta cadastrado.',
			]);
		}

		if (! $this->reparticaoService->buscarPorId((int) $dados['reparticao_id'])) {
			throw ValidationException::withMessages([
				'reparticao_id' => 'A reparticao informada nao foi encontrada.',
			]);
		}
		
		// Cadastrar o novo roteador usando o serviço
		$this->roteadorService->cadastrar($dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador cadastrado com sucesso!',
		], 201);
	}

	// Atualizar um roteador existente
	public function update(Request $request, string $roteadorId): JsonResponse
	{
		$roteador = $this->roteadorService->buscarPorId((int) $roteadorId);

		if (! $roteador) {
			return response()->json(['mensagem' => 'Roteador nao encontrado.'], 404);
		}

		$dados = $request->validate([
			'ip_roteador' => ['required', 'string', 'max:50'],
			'local_roteador' => ['required', 'string', 'max:255'],
			'usuario' => ['required', 'string', 'max:255'],
			'senha' => ['required', 'string', 'max:255'],
			'reparticao_id' => ['required', 'integer'],
		]);

		if ($this->roteadorService->existeIpRoteador($dados['ip_roteador'], (int) data_get($roteador, 'id'))) {
			throw ValidationException::withMessages([
				'ip_roteador' => 'Este IP de roteador ja esta cadastrado.',
			]);
		}

		if (! $this->reparticaoService->buscarPorId((int) $dados['reparticao_id'])) {
			throw ValidationException::withMessages([
				'reparticao_id' => 'A reparticao informada nao foi encontrada.',
			]);
		}

		// Atualizar o roteador usando o serviço
		$this->roteadorService->atualizar($roteador, $dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador atualizado com sucesso!',
		]);
	}

	// Excluir um roteador
	public function destroy(string $roteadorId): JsonResponse{
		$roteador = $this->roteadorService->buscarPorId((int) $roteadorId);

		if (! $roteador) {
			return response()->json(['mensagem' => 'Roteador nao encontrado.'], 404);
		}

		// Excluir o roteador usando o serviço
		$this->roteadorService->excluir($roteador);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador excluido com sucesso!',
		]);
	}

	// Listar os roteadores para uso em combo box (id, ip_roteador e nome_reparticao)
	public function combo(): JsonResponse {
		// Obter os roteadores para combo box usando o serviço e formatar os dados para incluir id, ip_roteador e nome_reparticao
		$dados = $this->roteadorService->listarParaCombobox()->map(fn ($roteador) => [
			'id' => data_get($roteador, 'id'),
			'ip' => data_get($roteador, 'ip_roteador') ?? data_get($roteador, 'ip'),
			'reparticao' => data_get($roteador, 'reparticao.nome_reparticao') ?? data_get($roteador, 'reparticao') ?? data_get($roteador, 'nome_reparticao'),
		]);

		return response()->json($dados);
	}

	// Obter o último roteador cadastrado
	public function ultimo(): JsonResponse {
		// Obter o último roteador cadastrado usando o serviço
		$ultimo = $this->roteadorService->obterUltimo();

		// Verificar se foi encontrado um roteador, caso contrário retornar resposta 204 No Content
		if (! $ultimo) {
			return response()->json(null, 204);
		}

		// Retornar os dados do último roteador em formato JSON, incluindo id, ip_roteador e nome_reparticao
		return response()->json([
			'id' => data_get($ultimo, 'id'),
			'ip' => data_get($ultimo, 'ip_roteador') ?? data_get($ultimo, 'ip'),
			'reparticao' => data_get($ultimo, 'reparticao.nome_reparticao') ?? data_get($ultimo, 'nome_reparticao'),
		]);
	}
}
