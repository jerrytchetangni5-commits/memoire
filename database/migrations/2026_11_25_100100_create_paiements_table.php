<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            // Un paiement conserve une trace comptable : on interdit de supprimer un mémoire payé
            $table->foreignId('memoire_id')->constrained('memoires')->restrictOnDelete();
            // Référence de la transaction chez Kkiapay : unique, donc une transaction ne sert qu'une fois
            $table->string('kkiapay_transaction_id', 100)->unique();
            $table->unsignedInteger('montant');
            $table->enum('statut', ['reussi', 'echoue']);
            // SHA-256 du jeton d'accès : le jeton en clair n'est jamais stocké
            $table->char('jeton_hash', 64)->nullable()->unique();
            $table->timestamps();

            $table->index(['memoire_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
