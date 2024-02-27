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
        Schema::create('produit_transfert_sis', function (Blueprint $table) {
            $table->increments('produit_transfert_si_id');
            $table->integer('transfer_si_id');
            $table->string('code');
            $table->integer('produit_id');
            $table->string('libelle');
            $table->integer('qte_recue');
            $table->integer('qte_transferee');
            $table->string('remarque');
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
        Schema::dropIfExists('produit_transfert_sis');
    }
};
