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
        Schema::create('soumissions', function (Blueprint $table) {
            $table->id('idSoumission');
            $table->integer('prixPropose');
            $table->text('description')->nullable();
            $table->integer('temps_realisation');
            $table->float('score_ia')->default(0);
            $table->string('fichier_joint')->nullable();
            $table->unsignedBigInteger('idUser');
            $table->foreign('idUser')->references('idUser')->on('users')->onDelete('cascade');
             $table->unsignedBigInteger('idAppel');
            $table->foreign('idAppel')->references('idAppel')->on('appelle_offres')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soumissions');
    }
};
