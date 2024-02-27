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
        Schema::create('commandes', function (Blueprint $table) {
            $table->increments('commande_id');
            $table->string('reference');
            $table->date('date_commande');
            $table->date('date_valide');
            $table->date('date_cloture');
            $table->date('date_livraison');
            $table->decimal('montant');
            $table->decimal('remise_par_montant');
            $table->decimal('remise_par_pourcentage');
            $table->decimal('net');
            $table->string('etat');
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
        Schema::dropIfExists('commandes');
    }
};
