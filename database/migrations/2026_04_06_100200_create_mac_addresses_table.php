<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mac_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address', 50)->unique();
            $table->string('nome_usuario');
            $table->string('funcao_usuario')->nullable();
            $table->string('dispositivo')->nullable();
            $table->date('data_cadastro')->default(DB::raw('(CURRENT_DATE)'));
            $table->foreignId('roteador_id')->constrained('roteadores')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_addresses');
    }
};
