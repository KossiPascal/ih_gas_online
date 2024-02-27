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
        Schema::create('produits', function (Blueprint $table) {
            $table->increments('produit_id');
            $table->string('reference');
            $table->string('nomCommercial');
            $table->string('dci');
            $table->string('unite');
            $table->string('familleTherapeutique');
            $table->integer('prixAchat');
            $table->integer('prixVente');
            $table->integer('stockMinimal');
            $table->integer('stockMaximal');
            $table->boolean('tobuy');
            $table->boolean('tosell');
            $table->integer('user_id');
            $table->boolean('statut');
            $table->integer('categorie_id');
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
        Schema::dropIfExists('produits');
    }
};
