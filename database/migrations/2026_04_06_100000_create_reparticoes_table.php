<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reparticoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_contato');
            $table->string('nome_reparticao');
            $table->string('endereco');
            $table->string('telefone', 50);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reparticoes');
    }
};
