<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reparticao;
use App\Services\ReparticaoService;
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

		// Listar as repartições usando o serviço, aplicando o filtro se fornecido
		$reparticoes = $this->reparticaoService->listar($filtro !== '' ? $filtro : null);

		return response()->json($reparticoes->map(fn (Reparticao $reparticao) => [
			'id' => $reparticao->id,
			'nome_contato' => $reparticao->nome_contato,
			'nome_reparticao' => $reparticao->nome_reparticao,
			'telefone' => $reparticao->telefone,
			'endereco' => $reparticao->endereco,
			'observacoes' => $reparticao->observacoes,
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
	public function update(Request $request, Reparticao $reparticao): JsonResponse {
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
	public function destroy(Reparticao $reparticao): JsonResponse {
		// Excluir a repartição usando o serviço
		$this->reparticaoService->excluir($reparticao);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao excluida com sucesso!',
		]);
	}

	// Listar as repartições para uso em combo box (id e nome_reparticao)
	public function combo(): JsonResponse {
		return response()->json($this->reparticaoService->listarParaCombo());
	}
}
