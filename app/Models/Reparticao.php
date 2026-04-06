<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reparticao extends Model
{
	use HasFactory;

	protected $table = 'reparticoes';

	protected $fillable = [
		'nome_contato',
		'nome_reparticao',
		'endereco',
		'telefone',
		'observacoes',
	];

	public function roteadores(): HasMany
	{
		return $this->hasMany(Roteador::class);
	}
}
