<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('domaine_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idUser');
            $table->foreign('idUser')->references('idUser')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('idDomaine');
            $table->foreign('idDomaine')->references('idDomaine')->on('domaines')->onDelete('cascade');          
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('domaine_user');
    }
};
