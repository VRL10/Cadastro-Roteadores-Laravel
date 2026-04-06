<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reparticao;
use App\Services\ReparticaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReparticaoController extends Controller
{
	public function __construct(private readonly ReparticaoService $reparticaoService) {}

	public function index(Request $request): JsonResponse
	{
		$filtro = trim((string) $request->query('filtro', ''));
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

	public function store(Request $request): JsonResponse
	{
		$dados = $request->validate([
			'nome_contato' => ['required', 'string', 'max:255'],
			'nome_reparticao' => ['required', 'string', 'max:255'],
			'endereco' => ['required', 'string', 'max:255'],
			'telefone' => ['required', 'string', 'max:50'],
			'observacoes' => ['nullable', 'string', 'max:1000'],
		]);

		$this->reparticaoService->cadastrar($dados);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao cadastrada com sucesso!',
		], 201);
	}

	public function update(Request $request, Reparticao $reparticao): JsonResponse
	{
		$dados = $request->validate([
			'nome_contato' => ['required', 'string', 'max:255'],
			'nome_reparticao' => ['required', 'string', 'max:255'],
			'endereco' => ['required', 'string', 'max:255'],
			'telefone' => ['required', 'string', 'max:50'],
			'observacoes' => ['nullable', 'string', 'max:1000'],
		]);

		$this->reparticaoService->atualizar($reparticao, $dados);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao atualizada com sucesso!',
		]);
	}

	public function destroy(Reparticao $reparticao): JsonResponse
	{
		$this->reparticaoService->excluir($reparticao);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Reparticao excluida com sucesso!',
		]);
	}

	public function combo(): JsonResponse
	{
		return response()->json($this->reparticaoService->listarParaCombo());
	}
}
