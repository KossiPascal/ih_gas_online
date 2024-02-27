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
        Schema::create('correction_stocks', function (Blueprint $table) {
            $table->increments('correction_stock_id');
            $table->date('date_cs');
            $table->string('code_cs');
            $table->string('motif_cs');
            $table->integer('cout');
            $table->integer('magasin_id');
            $table->integer('centre_id');
            $table->integer('user_id');
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
        Schema::dropIfExists('correction_stocks');
    }
};
