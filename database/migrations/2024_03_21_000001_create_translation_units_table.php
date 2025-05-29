<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_units', function (Blueprint $table) {
            $table->id();
            $table->string('source_text');
            $table->string('source_language', 5);
            $table->string('target_language', 5);
            $table->text('target_text')->nullable();
            $table->string('project_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index(['source_language', 'target_language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_units');
    }
}; 