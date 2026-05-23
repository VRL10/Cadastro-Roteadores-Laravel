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
	// Injeção de dependência do serviço de roteador
	public function __construct(private readonly RoteadorService $roteadorService) {}

	// Listar os roteadores, com opção de filtro por texto
	public function index(Request $request): JsonResponse {
		// Obter o filtro de texto da query string, se fornecido
		$filtro = trim((string) $request->query('filtro', ''));

		// Listar os roteadores usando o serviço, aplicando o filtro se fornecido
		$roteadores = $this->roteadorService->listar($filtro !== '' ? $filtro : null);

		// Retornar os roteadores em formato JSON, incluindo informações da repartição relacionada
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

	// Cadastrar um novo roteador
	public function store(Request $request): JsonResponse {
		// Validar os dados de entrada para criar um novo roteador
		$dados = $request->validate([
			'ip_roteador' => ['required', 'string', 'max:50', 'unique:roteadores,ip_roteador'],
			'local_roteador' => ['required', 'string', 'max:255'],
			'usuario' => ['required', 'string', 'max:255'],
			'senha' => ['required', 'string', 'max:255'],
			'reparticao_id' => ['required', 'integer', 'exists:reparticoes,id'],
		]);
		
		// Cadastrar o novo roteador usando o serviço
		$this->roteadorService->cadastrar($dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador cadastrado com sucesso!',
		], 201);
	}

	// Atualizar um roteador existente
	public function update(Request $request, Roteador $roteador): JsonResponse
	{
		$dados = $request->validate([
			'ip_roteador' => ['required', 'string', 'max:50', 'unique:roteadores,ip_roteador,'.$roteador->id],
			'local_roteador' => ['required', 'string', 'max:255'],
			'usuario' => ['required', 'string', 'max:255'],
			'senha' => ['required', 'string', 'max:255'],
			'reparticao_id' => ['required', 'integer', 'exists:reparticoes,id'],
		]);

		// Atualizar o roteador usando o serviço
		$this->roteadorService->atualizar($roteador, $dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador atualizado com sucesso!',
		]);
	}

	// Excluir um roteador
	public function destroy(Roteador $roteador): JsonResponse{
		// Excluir o roteador usando o serviço, tratando a exceção caso existam MACs vinculados
		try {
			$this->roteadorService->excluir($roteador);
		} catch (QueryException) {
			return response()->json([
				'sucesso' => false,
				'mensagem' => 'Nao foi possivel excluir o roteador porque existem MACs vinculados.',
			], 400);
		}

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'Roteador excluido com sucesso!',
		]);
	}

	// Listar os roteadores para uso em combo box (id, ip_roteador e nome_reparticao)
	public function combo(): JsonResponse {
		// Obter os roteadores para combo box usando o serviço e formatar os dados para incluir id, ip_roteador e nome_reparticao
		$dados = $this->roteadorService->listarParaCombobox()->map(fn (Roteador $roteador) => [
			'id' => $roteador->id,
			'ip' => $roteador->ip_roteador,
			'reparticao' => $roteador->reparticao?->nome_reparticao,
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
			'id' => $ultimo->id,
			'ip' => $ultimo->ip_roteador,
			'reparticao' => $ultimo->reparticao?->nome_reparticao,
		]);
	}
}
