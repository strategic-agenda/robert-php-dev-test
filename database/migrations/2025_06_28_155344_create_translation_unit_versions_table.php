<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationUnitVersionsTable extends Migration
{
    public function up()
    {
        Schema::create('translation_unit_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('translation_unit_id');
            $table->text('translated_text');
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->unsignedInteger('version_number');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('translation_unit_id')
                ->references('id')
                ->on('translation_units')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('translation_unit_versions');
    }
}
