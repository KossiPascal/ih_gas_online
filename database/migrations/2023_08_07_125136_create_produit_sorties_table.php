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
        Schema::create('produit_sorties', function (Blueprint $table) {
            $table->increments('"produit_sortie_id" ');
            $table->integer('sortie_id');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->integer('qte');
            $table->integer('prix_achat');
            $table->integer('montant');
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
        Schema::dropIfExists('produit_sorties');
    }
};
