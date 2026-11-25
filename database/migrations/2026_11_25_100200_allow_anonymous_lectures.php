<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('lectures', function (Blueprint $table) {
            // Une lecture peut venir d'un externe payant, sans compte
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreignId('paiement_id')->nullable()->after('memoire_id')
                ->constrained('paiements')->nullOnDelete();
        });

        Schema::table('lectures', function (Blueprint $table) {
            // nullOnDelete : supprimer un compte ne fait pas baisser le compteur de lectures
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Impossible de remettre user_id en NOT NULL avec des lectures anonymes
        DB::table('lectures')->whereNull('user_id')->delete();

        Schema::table('lectures', function (Blueprint $table) {
            $table->dropForeign(['paiement_id']);
            $table->dropColumn('paiement_id');
            $table->dropForeign(['user_id']);
        });

        Schema::table('lectures', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
