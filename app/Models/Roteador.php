<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roteador extends Model
{
	use HasFactory;

	protected $table = 'roteadores';

	protected $fillable = [
		'ip_roteador',
		'local_roteador',
		'usuario',
		'senha',
		'reparticao_id',
	];

	public function reparticao(): BelongsTo
	{
		return $this->belongsTo(Reparticao::class);
	}

	public function enderecosMac(): HasMany
	{
		return $this->hasMany(MacAddress::class, 'roteador_id');
	}
}
