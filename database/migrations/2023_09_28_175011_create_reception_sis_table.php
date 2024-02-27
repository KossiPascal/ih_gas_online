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
        Schema::create('reception_sis', function (Blueprint $table) {
            $table->increments('reception_si_id');
            $table->string('code');
            $table->date('date_reception');
            $table->decimal('montant');
            $table->string('etat')->nullable(true);
            $table->integer('commande_id');
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
        Schema::dropIfExists('reception_sis');
    }
};
