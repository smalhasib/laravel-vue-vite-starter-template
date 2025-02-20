<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Source types: URL, URL List, WordPress
            $table->string('type')->comment('Available types: URL, URL List, WordPress');
            $table->string('title')->nullable();
            // Status types: Queued, Indexing, Indexed
            $table->string('status')->comment('Available types: Queued, Indexing, Indexed');
            // Refresh schedule types: never, daily, weekly, monthly
            $table->string('refresh_schedule')->default('never')
                ->comment('Available types: never, daily, weekly, monthly');
            $table->boolean('process_images')->default(false);
            $table->timestamps();
            $table->timestamp('last_refresh_at')->nullable();
            $table->timestamp('next_refresh_at')->nullable();
        });

        Schema::create('url_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('url_list_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->json('urls')->comment('Array of URLs to process');
            $table->timestamps();
        });

        Schema::create('wordpress_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('xml_file_path')->comment('Path to uploaded WordPress XML export file');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_sources');
        Schema::dropIfExists('url_list_sources');
        Schema::dropIfExists('url_sources');
        Schema::dropIfExists('sources');
    }
};
