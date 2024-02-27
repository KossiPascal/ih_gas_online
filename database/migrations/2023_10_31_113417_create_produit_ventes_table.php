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
        Schema::create('produit_ventes', function (Blueprint $table) {
            $table->increments('produit_vente_id');
            $table->integer('vente_id');
            $table->string('code');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->integer('lot');
            $table->integer('pu');
            $table->integer('base');
            $table->integer('montant');
            $table->integer('pec');
            $table->integer('net');
            $table->integer('taux');
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
        Schema::dropIfExists('produit_ventes');
    }
};
