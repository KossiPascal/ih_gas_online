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
        Schema::create('stock_produits', function (Blueprint $table) {
            $table->increments('stock_produit_id');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->string('lot');
            $table->integer('magasin_id');
            $table->integer('centre_id');
            $table->integer('qte');
            $table->date('date_peremption');
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
        Schema::dropIfExists('stock_produits');
    }
};
