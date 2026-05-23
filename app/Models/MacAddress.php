<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacAddress extends Model
{
	// Usar a trait HasFactory para permitir a criação de fábricas de modelos
	use HasFactory;

	// Definir o nome da tabela associada a este modelo
	protected $table = 'mac_addresses';

	// Definir os campos que podem ser preenchidos em massa
	protected $fillable = [
		'mac_address',
		'nome_usuario',
		'funcao_usuario',
		'dispositivo',
		'data_cadastro',
		'roteador_id',
	];

	// Definir os casts para os campos, garantindo que data_cadastro seja tratada como uma data formatada
	protected $casts = [
		'data_cadastro' => 'date:Y-m-d',
	];

	// Definir a relação de muitos para um com o modelo Roteador
	public function roteador(): BelongsTo {
		return $this->belongsTo(Roteador::class);
	}
}
