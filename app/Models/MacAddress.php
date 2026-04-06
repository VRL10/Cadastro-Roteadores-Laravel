<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacAddress extends Model
{
	use HasFactory;

	protected $table = 'mac_addresses';

	protected $fillable = [
		'mac_address',
		'nome_usuario',
		'funcao_usuario',
		'dispositivo',
		'data_cadastro',
		'roteador_id',
	];

	protected $casts = [
		'data_cadastro' => 'date:Y-m-d',
	];

	public function roteador(): BelongsTo
	{
		return $this->belongsTo(Roteador::class);
	}
}
