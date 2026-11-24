<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('memoire_id')->constrained('memoires')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'memoire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};