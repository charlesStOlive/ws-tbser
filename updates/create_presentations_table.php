<?php namespace Waka\Tbser\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreatePresentationsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_tbser_presentations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('state')->default('Actif');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('slides')->nullable();
            $table->string('output_name')->nullable();
            $table->boolean('is_lot')->nullable()->default(true);
            $table->text('debug_data')->nullable();
            //reorder
            $table->integer('sort_order')->default(0);
            //softDelete
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_tbser_presentations');
    }
}