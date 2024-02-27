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
        Schema::create('mouvements', function (Blueprint $table) {
            $table->increments('mouvement_stock_id');
            $table->date('date');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->integer('magasin_id');
            $table->integer('centre_id');
            $table->integer('qte_initiale');
            $table->integer('qte_entree');
            $table->integer('qte_sortie');
            $table->integer('qte_reelle');
            $table->string('op_id');
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
        Schema::dropIfExists('mouvements');
    }
};
