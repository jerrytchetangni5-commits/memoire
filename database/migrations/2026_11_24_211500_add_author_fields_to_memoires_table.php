<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memoires', function (Blueprint $table) {
            $table->string('auteur_nom', 100)->nullable();
            $table->string('auteur_prenom', 100)->nullable();
            $table->foreignId('user_id')->nullable()->change();
        });

        DB::table('memoires')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunkById(100, function ($memoires): void {
                $users = DB::table('users')
                    ->whereIn('id', $memoires->pluck('user_id')->unique())
                    ->get(['id', 'nom', 'prenom'])
                    ->keyBy('id');

                foreach ($memoires as $memoire) {
                    $user = $users->get($memoire->user_id);

                    if ($user) {
                        DB::table('memoires')
                            ->where('id', $memoire->id)
                            ->update([
                                'auteur_nom' => $user->nom,
                                'auteur_prenom' => $user->prenom,
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (DB::table('memoires')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Cannot restore required user_id while author-only memories exist.');
        }

        Schema::table('memoires', function (Blueprint $table) {
            $table->dropColumn(['auteur_nom', 'auteur_prenom']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
