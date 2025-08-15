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
        Schema::table('appelle_offres', function (Blueprint $table) {
            // string(255) suffit pour le chemin 'fichiers_appels/xxx.pdf'
            $table->string('fichier_joint')->nullable()->after('date_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appelle_offres', function (Blueprint $table) {
            //
        });
    }
};
