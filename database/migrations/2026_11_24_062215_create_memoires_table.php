<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memoires', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 255);
            $table->text('resume');
            $table->string('mots_cles', 255)->nullable();
            $table->string('encadreur', 150)->nullable();

            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('filiere_id')->constrained('filieres')->restrictOnDelete();
            $table->foreignId('annee_academique_id')->constrained('annees_academiques')->restrictOnDelete();

            $table->string('fichier', 255);

            $table->enum('statut', ['soumis', 'en_verification', 'valide', 'rejete'])
                ->default('soumis');
            $table->text('motif_rejet')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('date_validation')->nullable();

            $table->boolean('telechargement_autorise')->default(false);
            $table->date('date_embargo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memoires');
    }
};