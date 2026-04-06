<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roteador;
use App\Services\RoteadorService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoteadorController extends Controller
{
	public function __construct(private readonly RoteadorService $roteadorService) {}

	public function index(Request $request): JsonResponse
	{
		$filtro = trim((string) $request->query('filtro', ''));
		$roteadores = $this->roteadorService->listar($filtro !== '' ? $filtro : null);

		return response()->json($roteadores->map(fn (Roteador $roteador) => [
			'id' => $roteador->id,
			'ip_roteador' => $roteador->ip_roteador,
			'local_roteador' => $roteador->local_roteador,
			'usuario' => $roteador->usuario,
			'senha' => $roteador->senha,
			'reparticao_id' => $roteador->reparticao_id,
			'nome_reparticao' => $roteador->reparticao?->nome_reparticao,
		]));
	}

	public function store(Request $request): JsonResponse
	{
		$dados = $request->validate([
			'ip_roteador' => ['required', 'string', 'max:50', 'unique:roteadores,ip_roteador'],
			'local_roteador' => ['required', 'string', 'max:255'],
			'usuario' => ['required', 'string', 'max:255'],
			'senha' => ['required', 'string', 'max:255'],
			'reparticao_id' => ['required', 'integer', 'exists:reparticoes,id'],
		]);

		$this->roteadorService->cadastrar($dados);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador cadastrado com sucesso!',
		], 201);
	}

	public function update(Request $request, Roteador $roteador): JsonResponse
	{
		$dados = $request->validate([
			'ip_roteador' => ['required', 'string', 'max:50', 'unique:roteadores,ip_roteador,'.$roteador->id],
			'local_roteador' => ['required', 'string', 'max:255'],
			'usuario' => ['required', 'string', 'max:255'],
			'senha' => ['required', 'string', 'max:255'],
			'reparticao_id' => ['required', 'integer', 'exists:reparticoes,id'],
		]);

		$this->roteadorService->atualizar($roteador, $dados);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador atualizado com sucesso!',
		]);
	}

	public function destroy(Roteador $roteador): JsonResponse
	{
		try {
			$this->roteadorService->excluir($roteador);
		} catch (QueryException) {
			return response()->json([
				'sucesso' => false,
				'mensagem' => 'Nao foi possivel excluir o roteador porque existem MACs vinculados.',
			], 400);
		}

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador excluido com sucesso!',
		]);
	}

	public function combo(): JsonResponse
	{
		$dados = $this->roteadorService->listarParaCombobox()->map(fn (Roteador $roteador) => [
			'id' => $roteador->id,
			'ip' => $roteador->ip_roteador,
			'reparticao' => $roteador->reparticao?->nome_reparticao,
		]);

		return response()->json($dados);
	}

	public function ultimo(): JsonResponse
	{
		$ultimo = $this->roteadorService->obterUltimo();

		if (! $ultimo) {
			return response()->json(null, 204);
		}

		return response()->json([
			'id' => $ultimo->id,
			'ip' => $ultimo->ip_roteador,
			'reparticao' => $ultimo->reparticao?->nome_reparticao,
		]);
	}
}
