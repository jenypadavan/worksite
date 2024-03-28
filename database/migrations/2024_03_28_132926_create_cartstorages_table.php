<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartstoragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cartstorages'))
            Schema::create('cartstorages', function (Blueprint $table) {
                $table->id();
                $table->integer('id_name');
                $table->integer('id_mod');
                $table->integer('sh_code');
                $table->integer('status');
                $table->string('disloc');
                $table->integer('cin');
                $table->integer('act');
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
        Schema::dropIfExists('cartstorages');
    }
}
