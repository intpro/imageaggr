<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resizes', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');

            $table->string('entity_name');
            $table->integer('entity_id');
            $table->string('image_name');
            $table->string('alt');
            $table->string('link');
            $table->integer('cache_index');
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
        Schema::drop('resizes');
    }
}
