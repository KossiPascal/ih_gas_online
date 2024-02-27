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
        Schema::create('produit_receptions', function (Blueprint $table) {
            $table->increments('"produit_reception_id" ');
            $table->integer('reception_id');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->string('lot');
            $table->integer('qte');
            $table->integer('prix_achat');
            $table->integer('montant');
            $table->date('date_fabrication');
            $table->date('date_expiration');
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
        Schema::dropIfExists('produit_receptions');
    }
};
