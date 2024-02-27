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
        Schema::create('produit_factures', function (Blueprint $table) {
            $table->increments('produit_facture_id');
            $table->integer('facture_id');
            $table->string('code');
            $table->integer('produit_id');
            $table->string('reference');
            $table->string('libelle');
            $table->string('lot');
            $table->integer('qte');
            $table->integer('prix_public');
            $table->integer('base_assurance');
            $table->integer('total');
            $table->integer('taux');
            $table->integer('prise_encharge');
            $table->integer('part_patient');
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
        Schema::dropIfExists('produit_factures');
    }
};
