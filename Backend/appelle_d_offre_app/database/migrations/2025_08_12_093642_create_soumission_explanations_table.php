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
               Schema::create('soumission_explanations', function (Blueprint $table) {
           $table->unsignedBigInteger('soumission_id');
$table->foreign('soumission_id')
      ->references('idSoumission')
      ->on('soumissions')
      ->onDelete('cascade');

            $table->string('verdict');                // 'acceptée' | 'refusée'
            $table->json('categories')->nullable();   // ex: ["budget_over","delay_over"]
            $table->string('public_phrase')->nullable();
            $table->timestamps();

            $table->unique('soumission_id');
        });
    }


    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soumission_explanations');
    }
};
