<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_unit_id')->constrained()->onDelete('cascade');
            $table->text('previous_text');
            $table->text('new_text');
            $table->string('changed_by');
            $table->string('change_reason')->nullable();
            $table->timestamps();

            $table->index('translation_unit_id');
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_histories');
    }
}; 