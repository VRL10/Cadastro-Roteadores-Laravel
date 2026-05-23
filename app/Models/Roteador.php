<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roteador extends Model
{
	// Usar a trait HasFactory para permitir a criação de fábricas de modelos
	use HasFactory;

	// Definir o nome da tabela associada a este modelo
	protected $table = 'roteadores';

	// Definir os campos que podem ser preenchidos em massa
	protected $fillable = [
		'ip_roteador',
		'local_roteador',
		'usuario',
		'senha',
		'reparticao_id',
	];

	// Definir a relação de muitos para um com o modelo Reparticao
	public function reparticao(): BelongsTo {
		return $this->belongsTo(Reparticao::class);
	}

	// Definir a relação de um para muitos com o modelo MacAddress
	public function enderecosMac(): HasMany {
		return $this->hasMany(MacAddress::class, 'roteador_id');
	}
}