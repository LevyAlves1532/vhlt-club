<?php

use App\Enum\TournamentStatusEnum;
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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->references('id')->on('communities');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description');
            $table->text('description');
            $table->string('status')->default(TournamentStatusEnum::OUTLINE);
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
