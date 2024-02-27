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
        Schema::create('operation_bancaires', function (Blueprint $table) {
            $table->increments('operation_id');
            $table->date('date');
            $table->string('type_operation');
            $table->string('libelle');
            $table->string('operant');
            $table->double('initiale');
            $table->double('entree');
            $table->double('sortie');
            $table->double('solde');
            $table->integer('user_id');
            $table->integer('centre_id');
            $table->integer('compte_id')->nullable(true);
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
        Schema::dropIfExists('operation_bancaires');
    }
};
