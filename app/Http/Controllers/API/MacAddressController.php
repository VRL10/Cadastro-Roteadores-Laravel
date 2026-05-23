<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MacAddress;
use App\Services\MacAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MacAddressController extends Controller
{
	// Injeção de dependência do serviço de MAC address
	public function __construct(private readonly MacAddressService $macAddressService) {}

	// Listar os MAC addresses, com opção de filtro por roteador e por texto
	public function index(Request $request): JsonResponse
	{
		// Obter o filtro de texto da query string, se fornecido
		$filtro = trim((string) $request->query('filtro', ''));

		// Listar os MAC addresses usando o serviço, aplicando os filtros se fornecidos
		$macs = $this->macAddressService->listar(
			roteadorId: $request->query('roteador_id') ? (int) $request->query('roteador_id') : null,
			filtro: $filtro !== '' ? $filtro : null,
		);

		// Retornar os MAC addresses em formato JSON, incluindo informações do roteador e repartição relacionadas
		return response()->json($macs->map(fn (MacAddress $mac) => [
			'id' => $mac->id,
			'mac_address' => $mac->mac_address,
			'nome_usuario' => $mac->nome_usuario,
			'funcao_usuario' => $mac->funcao_usuario,
			'dispositivo' => $mac->dispositivo,
			'data_cadastro' => $mac->data_cadastro?->format('Y-m-d'),
			'ip_roteador' => $mac->roteador?->ip_roteador,
			'nome_reparticao' => $mac->roteador?->reparticao?->nome_reparticao,
			'roteador_id' => $mac->roteador_id,
		]));
	}

	// Cadastrar um novo MAC address
	public function store(Request $request): JsonResponse
	{
		// Validar os dados de entrada para criar um novo MAC address
		$dados = $request->validate([
			'mac_address' => ['required', 'string', 'max:50', 'unique:mac_addresses,mac_address'],
			'nome_usuario' => ['required', 'string', 'max:255'],
			'funcao_usuario' => ['nullable', 'string', 'max:255'],
			'dispositivo' => ['nullable', 'string', 'max:255'],
			'roteador_id' => ['required', 'integer', 'exists:roteadores,id'],
		]);

		// Cadastrar o novo MAC address usando o serviço
		$this->macAddressService->cadastrar($dados);
		
		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address cadastrado com sucesso!',
		], 201);
	}

	// Atualizar um MAC address existente
	public function update(Request $request, MacAddress $mac): JsonResponse
	{
		// Validar os dados de entrada para atualizar o MAC address
		$dados = $request->validate([
			'mac_address' => ['required', 'string', 'max:50', 'unique:mac_addresses,mac_address,'.$mac->id],
			'nome_usuario' => ['required', 'string', 'max:255'],
			'funcao_usuario' => ['nullable', 'string', 'max:255'],
			'dispositivo' => ['nullable', 'string', 'max:255'],
			'roteador_id' => ['required', 'integer', 'exists:roteadores,id'],
		]);

		// Atualizar o MAC address usando o serviço
		$this->macAddressService->atualizar($mac, $dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address atualizado com sucesso!',
		]);
	}

	// Excluir um MAC address
	public function destroy(MacAddress $mac): JsonResponse
	{
		// Excluir o MAC address usando o serviço
		$this->macAddressService->excluir($mac);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address excluido com sucesso!',
		]);
	}
}
