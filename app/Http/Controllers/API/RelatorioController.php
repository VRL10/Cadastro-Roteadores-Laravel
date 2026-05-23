<?php

namespace App\Http\Controllers\API;

use App\Exports\MacsExport;
use App\Exports\ReparticoesExport;
use App\Exports\RoteadorDetalhadoExport;
use App\Http\Controllers\Controller;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
	// Injeção de dependência do serviço de relatórios
	public function __construct(private readonly RelatorioService $relatorioService) {}

	// Gerar relatório PDF das repartições, incluindo quantidade de roteadores e MAC addresses
	public function reparticoesPdf() {
		// Obter os dados das repartições, incluindo quantidade de roteadores e MAC addresses
		$reparticoes = $this->relatorioService->dadosReparticoes();

		// Gerar o PDF usando a view 'relatorios.reparticoes-pdf' e os dados das repartições
		$pdf = Pdf::loadView('relatorios.reparticoes-pdf', [
			'reparticoes' => $reparticoes,
			'geradoEm' => now(),
		])->setPaper('a4', 'landscape');

		return $pdf->download('relatorio_reparticoes_'.now()->format('Ymd_His').'.pdf');
	}

	// Gerar relatório Excel das repartições, incluindo quantidade de roteadores e MAC addresses
	public function reparticoesExcel()
	{
		return Excel::download(new ReparticoesExport($this->relatorioService->dadosReparticoes()), 'relatorio_reparticoes_'.now()->format('Ymd_His').'.xlsx');
	}

	// Gerar relatório PDF detalhado de um roteador específico, incluindo seus MAC addresses
	public function roteadorPdf(string $ip){
		// Obter os dados do roteador por IP, incluindo seus MAC addresses
		$roteador = $this->relatorioService->dadosRoteadorPorIp($ip);

		// Verificar se o roteador foi encontrado
		if (! $roteador) {
			return response()->json(['mensagem' => 'Roteador nao encontrado.'], 404);
		}

		// Gerar o PDF usando a view 'relatorios.roteador-pdf' e os dados do roteador
		$pdf = Pdf::loadView('relatorios.roteador-pdf', [
			'roteador' => $roteador,
			'geradoEm' => now(),
		])->setPaper('a4', 'portrait');

		return $pdf->download('relatorio_roteador_'.Str::slug($ip, '_').'_'.now()->format('Ymd_His').'.pdf');
	}

	// Gerar relatório Excel detalhado de um roteador específico, incluindo seus MAC addresses
	public function roteadorExcel(string $ip){
		// Obter os dados do roteador por IP, incluindo seus MAC addresses
		$roteador = $this->relatorioService->dadosRoteadorPorIp($ip);

		// Verificar se o roteador foi encontrado
		if (! $roteador) {
			return response()->json(['mensagem' => 'Roteador nao encontrado.'], 404);
		}

		// Gerar o Excel usando a exportação RoteadorDetalhadoExport e os dados do roteador
		return Excel::download(new RoteadorDetalhadoExport($roteador), 'relatorio_roteador_'.Str::slug($ip, '_').'_'.now()->format('Ymd_His').'.xlsx');
	}

	// Gerar relatório PDF dos MAC addresses, incluindo informações do roteador e repartição
	public function macsPdf(){
		// Obter os dados dos MAC addresses, incluindo informações do roteador e repartição
		$macs = $this->relatorioService->dadosMacs();

		// Gerar o PDF usando a view 'relatorios.macs-pdf' e os dados dos MAC addresses
		$pdf = Pdf::loadView('relatorios.macs-pdf', [
			'macs' => $macs,
			'geradoEm' => now(),
		])->setPaper('a4', 'landscape');

		// Retornar o PDF para download, com um nome de arquivo que inclui a data e hora de geração
		return $pdf->download('relatorio_macs_'.now()->format('Ymd_His').'.pdf');
	}

	// Gerar relatório Excel dos MAC addresses, incluindo informações do roteador e repartição
	public function macsExcel()
	{
		return Excel::download(new MacsExport($this->relatorioService->dadosMacs()), 'relatorio_macs_'.now()->format('Ymd_His').'.xlsx');
	}
}
