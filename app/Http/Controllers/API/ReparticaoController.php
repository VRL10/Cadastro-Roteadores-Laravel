<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ReparticaoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReparticaoController extends Controller
{
	// Injeção de dependência do serviço de repartição
	public function __construct(private readonly ReparticaoService $reparticaoService) {}
	
	// Listar as repartições, com opção de filtro por texto
	public function index(Request $request): JsonResponse {
		// Obter o filtro de texto da query string, se fornecido
		$filtro = trim((string) $request->query('filtro', ''));

		try {
			// Listar as repartições usando o serviço, aplicando o filtro se fornecido
			$reparticoes = $this->reparticaoService->listar($filtro !== '' ? $filtro : null);
		} catch (QueryException) {
			return response()->json([]);
		}

		return response()->json($reparticoes->map(fn ($reparticao) => [
			'id' => data_get($reparticao, 'id'),
			'nome_contato' => data_get($reparticao, 'nome_contato'),
			'nome_reparticao' => data_get($reparticao, 'nome_reparticao'),
			'telefone' => data_get($reparticao, 'telefone'),
			'endereco' => data_get($reparticao, 'endereco'),
			'observacoes' => data_get($reparticao, 'observacoes'),
		]));
	}

	public function store(Request $request): JsonResponse {
		// Validar os dados de entrada para criar uma nova repartição
		$dados = $request->validate([
			'nome_contato' => ['required', 'string', 'max:255'],
			'nome_reparticao' => ['required', 'string', 'max:255'],
			'endereco' => ['required', 'string', 'max:255'],
			'telefone' => ['required', 'string', 'max:50'],
			'observacoes' => ['nullable', 'string', 'max:1000'],
		]);

		// Cadastrar a nova repartição usando o serviço
		$this->reparticaoService->cadastrar($dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao cadastrada com sucesso!',
		], 201);
	}

	// Atualizar uma repartição existente
	public function update(Request $request, string $reparticaoId): JsonResponse {
		$reparticao = $this->reparticaoService->buscarPorId((int) $reparticaoId);

		if (! $reparticao) {
			return response()->json(['mensagem' => 'Reparticao nao encontrada.'], 404);
		}

		// Validar os dados de entrada para atualizar a repartição
		$dados = $request->validate([
			'nome_contato' => ['required', 'string', 'max:255'],
			'nome_reparticao' => ['required', 'string', 'max:255'],
			'endereco' => ['required', 'string', 'max:255'],
			'telefone' => ['required', 'string', 'max:50'],
			'observacoes' => ['nullable', 'string', 'max:1000'],
		]);

		// Atualizar a repartição usando o serviço
		$this->reparticaoService->atualizar($reparticao, $dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao atualizada com sucesso!',
		]);
	}

	// Excluir uma repartição
	public function destroy(string $reparticaoId): JsonResponse {
		$reparticao = $this->reparticaoService->buscarPorId((int) $reparticaoId);

		if (! $reparticao) {
			return response()->json(['mensagem' => 'Reparticao nao encontrada.'], 404);
		}

		// Excluir a repartição usando o serviço
		$this->reparticaoService->excluir($reparticao);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao excluida com sucesso!',
		]);
	}

	// Listar as repartições para uso em combo box (id e nome_reparticao)
	public function combo(): JsonResponse {
		try {
			return response()->json($this->reparticaoService->listarParaCombo()->map(fn ($reparticao) => [
				'id' => data_get($reparticao, 'id'),
				'nome' => data_get($reparticao, 'nome') ?? data_get($reparticao, 'nome_reparticao'),
			]));
		} catch (QueryException) {
			return response()->json([]);
		}
	}
}
