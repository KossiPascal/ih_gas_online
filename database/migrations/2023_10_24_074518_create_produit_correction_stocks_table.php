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
        Schema::create('produit_correction_stocks', function (Blueprint $table) {
            $table->increments('produit_correction_stock_id');
            $table->integer('correction_stock_id');
            $table->integer('stock_produit_id');
            $table->string('code_cs');
            $table->string('motif');
            $table->integer('qte');
            $table->integer('pu');
            $table->integer('cout');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->string('lot');
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
        Schema::dropIfExists('produit_correction_stocks');
    }
};
