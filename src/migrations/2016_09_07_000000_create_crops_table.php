<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCropsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crops', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');

            $table->string('entity_name');
            $table->integer('entity_id');
            $table->string('image_name');
            $table->string('alt');
            $table->string('link');
            $table->string('man_name');
            $table->string('target_name');
            $table->integer('cache_index');
            $table->integer('x');
            $table->integer('y');
            $table->integer('width');
            $table->integer('height');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('crops');
    }
}
