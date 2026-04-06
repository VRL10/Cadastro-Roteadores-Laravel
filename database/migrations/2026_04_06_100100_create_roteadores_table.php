<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roteadores', function (Blueprint $table) {
            $table->id();
            $table->string('ip_roteador', 50)->unique();
            $table->string('local_roteador');
            $table->string('usuario');
            $table->string('senha');
            $table->foreignId('reparticao_id')->constrained('reparticoes')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roteadores');
    }
};
