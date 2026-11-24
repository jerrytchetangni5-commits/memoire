<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Liste de référence alimentée par l'administration (import hors scope ici) :
// une inscription n'est acceptée que si le couple matricule/e-mail y figure déjà.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etudiants_eneam', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 8)->unique();
            $table->string('email', 150)->unique();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiants_eneam');
    }
};