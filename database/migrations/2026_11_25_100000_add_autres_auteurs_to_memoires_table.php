<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memoires', function (Blueprint $table) {
            // Noms saisis à la main : aucun lien avec la table users
            $table->string('autres_auteurs', 255)->nullable()->after('encadreur');
        });
    }

    public function down(): void
    {
        Schema::table('memoires', function (Blueprint $table) {
            $table->dropColumn('autres_auteurs');
        });
    }
};
