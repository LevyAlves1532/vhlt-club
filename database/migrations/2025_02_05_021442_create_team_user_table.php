<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->references('id')->on('teams');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->boolean('is_allowed')->default(false)->comment('O usuário foi convidado por alguém do time ou o lider aceitou o usuário ao time');
            $table->boolean('is_accepted')->default(false)->comment('O usuário pediu para entrar no grupo ou aceitou o pedido feito por alguém do time');
            $table->boolean('is_leader')->default(false)->comment('O usuário tem o cargo mais alto do time sendo o lider, aquele que gerencia o time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
