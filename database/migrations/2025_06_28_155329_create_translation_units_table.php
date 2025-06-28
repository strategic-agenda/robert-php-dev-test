<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationUnitsTable extends Migration
{
    public function up()
    {
        Schema::create('translation_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_id');
            $table->unsignedInteger('segment_index');
            $table->text('source_text');
            $table->string('source_locale', 5);
            $table->string('target_locale', 5);
            $table->timestamps();

            $table->index(['document_id', 'source_locale', 'target_locale']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('translation_units');
    }
}
