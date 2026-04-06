<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MacAddress;
use App\Services\MacAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MacAddressController extends Controller
{
	public function __construct(private readonly MacAddressService $macAddressService) {}

	public function index(Request $request): JsonResponse
	{
		$filtro = trim((string) $request->query('filtro', ''));
		$macs = $this->macAddressService->listar(
			roteadorId: $request->query('roteador_id') ? (int) $request->query('roteador_id') : null,
			filtro: $filtro !== '' ? $filtro : null,
		);

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

	public function store(Request $request): JsonResponse
	{
		$dados = $request->validate([
			'mac_address' => ['required', 'string', 'max:50', 'unique:mac_addresses,mac_address'],
			'nome_usuario' => ['required', 'string', 'max:255'],
			'funcao_usuario' => ['nullable', 'string', 'max:255'],
			'dispositivo' => ['nullable', 'string', 'max:255'],
			'roteador_id' => ['required', 'integer', 'exists:roteadores,id'],
		]);

		$this->macAddressService->cadastrar($dados);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address cadastrado com sucesso!',
		], 201);
	}

	public function update(Request $request, MacAddress $mac): JsonResponse
	{
		$dados = $request->validate([
			'mac_address' => ['required', 'string', 'max:50', 'unique:mac_addresses,mac_address,'.$mac->id],
			'nome_usuario' => ['required', 'string', 'max:255'],
			'funcao_usuario' => ['nullable', 'string', 'max:255'],
			'dispositivo' => ['nullable', 'string', 'max:255'],
			'roteador_id' => ['required', 'integer', 'exists:roteadores,id'],
		]);

		$this->macAddressService->atualizar($mac, $dados);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address atualizado com sucesso!',
		]);
	}

	public function destroy(MacAddress $mac): JsonResponse
	{
		$this->macAddressService->excluir($mac);

		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address excluido com sucesso!',
		]);
	}
}
