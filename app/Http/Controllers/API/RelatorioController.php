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
	public function __construct(private readonly RelatorioService $relatorioService) {}

	public function reparticoesPdf()
	{
		$reparticoes = $this->relatorioService->dadosReparticoes();
		$pdf = Pdf::loadView('relatorios.reparticoes-pdf', [
			'reparticoes' => $reparticoes,
			'geradoEm' => now(),
		])->setPaper('a4', 'landscape');

		return $pdf->download('relatorio_reparticoes_'.now()->format('Ymd_His').'.pdf');
	}

	public function reparticoesExcel()
	{
		return Excel::download(new ReparticoesExport($this->relatorioService->dadosReparticoes()), 'relatorio_reparticoes_'.now()->format('Ymd_His').'.xlsx');
	}

	public function roteadorPdf(string $ip)
	{
		$roteador = $this->relatorioService->dadosRoteadorPorIp($ip);

		if (! $roteador) {
			return response()->json(['mensagem' => 'Roteador nao encontrado.'], 404);
		}

		$pdf = Pdf::loadView('relatorios.roteador-pdf', [
			'roteador' => $roteador,
			'geradoEm' => now(),
		])->setPaper('a4', 'portrait');

		return $pdf->download('relatorio_roteador_'.Str::slug($ip, '_').'_'.now()->format('Ymd_His').'.pdf');
	}

	public function roteadorExcel(string $ip)
	{
		$roteador = $this->relatorioService->dadosRoteadorPorIp($ip);

		if (! $roteador) {
			return response()->json(['mensagem' => 'Roteador nao encontrado.'], 404);
		}

		return Excel::download(new RoteadorDetalhadoExport($roteador), 'relatorio_roteador_'.Str::slug($ip, '_').'_'.now()->format('Ymd_His').'.xlsx');
	}

	public function macsPdf()
	{
		$macs = $this->relatorioService->dadosMacs();
		$pdf = Pdf::loadView('relatorios.macs-pdf', [
			'macs' => $macs,
			'geradoEm' => now(),
		])->setPaper('a4', 'landscape');

		return $pdf->download('relatorio_macs_'.now()->format('Ymd_His').'.pdf');
	}

	public function macsExcel()
	{
		return Excel::download(new MacsExport($this->relatorioService->dadosMacs()), 'relatorio_macs_'.now()->format('Ymd_His').'.xlsx');
	}
}
