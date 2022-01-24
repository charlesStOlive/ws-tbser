<?php namespace Waka\Tbser\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreatePresentationsTableU110 extends Migration
{
    public function up()
    {
        Schema::table('waka_tbser_presentations', function (Blueprint $table) {
            $table->renameColumn('name_construction', 'output_name');
        });
    }

    public function down()
    {
        Schema::table('waka_tbser_presentations', function (Blueprint $table) {
            $table->renameColumn('output_name', 'name_construction');
        });
    }
}