<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('droit_profils', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('droit_id');
            $table->unsignedBigInteger('profil_id');
            $table->foreign('droit_id')->references('droit_id')->on('droits')->onDelete('cascade');
            $table->foreign('profil_id')->references('profil_id')->on('profils')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('droit_profils');
    }
};
