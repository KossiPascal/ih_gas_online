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
        Schema::create('ventes', function (Blueprint $table) {
            $table->increments('vente_id');
            $table->string('code');
            $table->date('date_vente');
            $table->integer('montant_total');
            $table->integer('prise_en_charge');
            $table->integer('net_apayer');
            $table->integer('montant_paye');
            $table->integer('montant_recu');
            $table->integer('reliquat');
            $table->string('etat');
            $table->integer('patient_id');
            $table->integer('assurance_id');
            $table->integer('user_id');
            $table->integer('centre_id');
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
        Schema::dropIfExists('ventes');
    }
};
