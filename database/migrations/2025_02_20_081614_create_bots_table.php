<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            // Model types: gpt-4o, gpt-4o-mini, gpt-4-turbo
            $table->string('model_type')->comment('Available types: gpt-4o, gpt-4o-mini, gpt-4-turbo');
            $table->string('language');
            // Status types: Ready, Awaiting Sources
            $table->string('status')->comment('Available types: Ready, Awaiting Sources');
            $table->integer('sources_count')->default(0);
            $table->integer('questions_count')->default(0);
            $table->integer('total_indexed_chunks_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};
