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
        Schema::create('factures', function (Blueprint $table) {
            $table->increments('facture_id');
            $table->string('code');
            $table->date('date');
            $table->integer('total');
            $table->integer('prise_encharge');
            $table->integer('part_patient');
            $table->integer('montant_recu');
            $table->integer('reliquat');
            $table->integer('user_id');
            $table->integer('centre_id');
            $table->integer('assurance_id');
            $table->integer('patient_id');
            $table->integer('magasin_id');
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
        Schema::dropIfExists('factures');
    }
};
