<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reparticao extends Model
{
	// Usar a trait HasFactory para permitir a criação de fábricas de modelos
	use HasFactory;

	// Definir o nome da tabela associada a este modelo
	protected $table = 'reparticoes';

	// Definir os campos que podem ser preenchidos em massa
	protected $fillable = [
		'nome_contato',
		'nome_reparticao',
		'endereco',
		'telefone',
		'observacoes',
	];

	// Definir a relação de um para muitos com o modelo Roteador
	public function roteadores(): HasMany{
		return $this->hasMany(Roteador::class);
	}
}
