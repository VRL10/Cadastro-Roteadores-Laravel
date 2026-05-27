<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MacAddressService;
use App\Services\RoteadorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MacAddressController extends Controller
{
	// Injeção de dependência do serviço de MAC address
	public function __construct(
		private readonly MacAddressService $macAddressService,
		private readonly RoteadorService $roteadorService,
	) {}

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
		return response()->json($macs->map(fn ($mac) => [
			'id' => data_get($mac, 'id'),
			'mac_address' => data_get($mac, 'mac_address'),
			'nome_usuario' => data_get($mac, 'nome_usuario'),
			'funcao_usuario' => data_get($mac, 'funcao_usuario'),
			'dispositivo' => data_get($mac, 'dispositivo'),
			'data_cadastro' => data_get($mac, 'data_cadastro')?->format('Y-m-d'),
			'ip_roteador' => data_get($mac, 'roteador.ip_roteador') ?? data_get($mac, 'ip_roteador'),
			'nome_reparticao' => data_get($mac, 'roteador.reparticao.nome_reparticao') ?? data_get($mac, 'nome_reparticao'),
			'roteador_id' => data_get($mac, 'roteador_id'),
		]));
	}

	// Cadastrar um novo MAC address
	public function store(Request $request): JsonResponse
	{
		// Validar os dados de entrada para criar um novo MAC address
		$dados = $request->validate([
			'mac_address' => ['required', 'string', 'max:50'],
			'nome_usuario' => ['required', 'string', 'max:255'],
			'funcao_usuario' => ['nullable', 'string', 'max:255'],
			'dispositivo' => ['nullable', 'string', 'max:255'],
			'roteador_id' => ['required', 'integer'],
		]);

		if ($this->macAddressService->existeMacAddress($dados['mac_address'])) {
			throw ValidationException::withMessages([
				'mac_address' => 'Este MAC address ja esta cadastrado.',
			]);
		}

		if (! $this->roteadorService->buscarPorId((int) $dados['roteador_id'])) {
			throw ValidationException::withMessages([
				'roteador_id' => 'O roteador informado nao foi encontrado.',
			]);
		}

		// Cadastrar o novo MAC address usando o serviço
		$this->macAddressService->cadastrar($dados);
		
		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address cadastrado com sucesso!',
		], 201);
	}

	// Atualizar um MAC address existente
	public function update(Request $request, string $macId): JsonResponse
	{
		$mac = $this->macAddressService->buscarPorId((int) $macId);

		if (! $mac) {
			return response()->json(['mensagem' => 'MAC address nao encontrado.'], 404);
		}

		// Validar os dados de entrada para atualizar o MAC address
		$dados = $request->validate([
			'mac_address' => ['required', 'string', 'max:50'],
			'nome_usuario' => ['required', 'string', 'max:255'],
			'funcao_usuario' => ['nullable', 'string', 'max:255'],
			'dispositivo' => ['nullable', 'string', 'max:255'],
			'roteador_id' => ['required', 'integer'],
		]);

		if ($this->macAddressService->existeMacAddress($dados['mac_address'], (int) data_get($mac, 'id'))) {
			throw ValidationException::withMessages([
				'mac_address' => 'Este MAC address ja esta cadastrado.',
			]);
		}

		if (! $this->roteadorService->buscarPorId((int) $dados['roteador_id'])) {
			throw ValidationException::withMessages([
				'roteador_id' => 'O roteador informado nao foi encontrado.',
			]);
		}

		// Atualizar o MAC address usando o serviço
		$this->macAddressService->atualizar($mac, $dados);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address atualizado com sucesso!',
		]);
	}

	// Excluir um MAC address
	public function destroy(string $macId): JsonResponse
	{
		$mac = $this->macAddressService->buscarPorId((int) $macId);

		if (! $mac) {
			return response()->json(['mensagem' => 'MAC address nao encontrado.'], 404);
		}

		// Excluir o MAC address usando o serviço
		$this->macAddressService->excluir($mac);

		// Retornar uma resposta JSON indicando sucesso
		return response()->json([
			'sucesso' => true,
			'mensagem' => 'MAC address excluido com sucesso!',
		]);
	}
}
