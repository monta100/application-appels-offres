<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('soumissions', function (Blueprint $table) {
        $table->float('score_ia_anomalie')->nullable();
        $table->string('verdict_ia_anomalie')->nullable();
        $table->text('explication_anomalie')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soumissions', function (Blueprint $table) {
            //
        });
    }
};
