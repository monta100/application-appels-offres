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
        Schema::create('appelle_offres', function (Blueprint $table) {
            $table->id('idAppel');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['brouillon', 'publiee', 'cloture'])->default('brouillon');
            $table->decimal('budget', 10, 2)->nullable();
            $table->dateTime('date_publication')->default(now());
           $table->unsignedBigInteger('idDomaine');
           $table->foreign('idDomaine')->references('idDomaine')->on('domaines')->onDelete('cascade');
             // 🔵 Clé étrangère vers users (clé primaire = idUser)
        $table->unsignedBigInteger('idUser');
        $table->foreign('idUser')->references('idUser')->on('users')->onDelete('cascade');
           
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appelle_offres');
    }
};
