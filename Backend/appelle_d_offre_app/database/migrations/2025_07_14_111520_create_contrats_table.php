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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id('idContrat');
            $table->string('fichier_pdf')->nullable();
            $table->dateTime('date_creation')->default(now());
            $table->unsignedBigInteger('idSoumission');
            $table->foreign('idSoumission')->references('idSoumission')->on('soumissions')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
