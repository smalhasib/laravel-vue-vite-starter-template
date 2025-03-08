<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Esign\DatabaseTrigger\DatabaseTrigger;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;
use Esign\DatabaseTrigger\Facades\Schema as TriggerSchema;

return new class extends Migration {
    public function up(): void
    {
        // Create sources table
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->comment('Available types: URL, URL List, WordPress');
            $table->string('title')->nullable();
            $table->enum('status', ['queued', 'indexing', 'indexed', 'failed'])->default('queued');
            $table->enum('refresh_schedule', ['never', 'daily', 'weekly', 'monthly'])->default('never');
            $table->integer('indexed_chunks_count')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->timestamp('last_refresh_at')->nullable();
            $table->timestamp('next_refresh_at')->nullable();
        });

        // Create documents table
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content')->nullable(); // Using text for larger content
            $table->string('source')->nullable(); // URL or reference
            $table->integer('indexed_chunks_count')->default(0);
            $table->timestamps();
        });

        // Create triggers for documents table to update source's indexed_chunks_count
        TriggerSchema::createTrigger('after_document_insert', function (DatabaseTrigger $trigger) {
            $trigger->on('documents');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::INSERT);
            $trigger->statement('
                UPDATE sources
                SET indexed_chunks_count = (
                    SELECT COALESCE(SUM(indexed_chunks_count), 0)
                    FROM documents
                    WHERE source_id = NEW.source_id
                ),
                updated_at = NOW()
                WHERE id = NEW.source_id;
            ');
        });

        TriggerSchema::createTrigger('after_document_update', function (DatabaseTrigger $trigger) {
            $trigger->on('documents');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::UPDATE);
            $trigger->statement('
                UPDATE sources
                SET indexed_chunks_count = (
                    SELECT COALESCE(SUM(indexed_chunks_count), 0)
                    FROM documents
                    WHERE source_id = NEW.source_id
                ),
                updated_at = NOW()
                WHERE id = NEW.source_id;
            ');
        });

        TriggerSchema::createTrigger('after_document_delete', function (DatabaseTrigger $trigger) {
            $trigger->on('documents');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::DELETE);
            $trigger->statement('
                UPDATE sources
                SET indexed_chunks_count = (
                    SELECT COALESCE(SUM(indexed_chunks_count), 0)
                    FROM documents
                    WHERE source_id = OLD.source_id
                ),
                updated_at = NOW()
                WHERE id = OLD.source_id;
            ');
        });

        // Create triggers for sources to update bot's total_indexed_chunks_count
        TriggerSchema::createTrigger('after_source_insert', function (DatabaseTrigger $trigger) {
            $trigger->on('sources');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::INSERT);
            $trigger->statement('
                UPDATE bots
                SET total_indexed_chunks_count = (
                    SELECT COALESCE(SUM(indexed_chunks_count), 0)
                    FROM sources
                    WHERE bot_id = NEW.bot_id
                ),
                updated_at = NOW()
                WHERE id = NEW.bot_id;
            ');
        });

        TriggerSchema::createTrigger('after_source_update', function (DatabaseTrigger $trigger) {
            $trigger->on('sources');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::UPDATE);
            $trigger->statement('
                UPDATE bots
                SET total_indexed_chunks_count = (
                    SELECT COALESCE(SUM(indexed_chunks_count), 0)
                    FROM sources
                    WHERE bot_id = NEW.bot_id
                ),
                updated_at = NOW()
                WHERE id = NEW.bot_id;
            ');
        });

        TriggerSchema::createTrigger('after_source_delete', function (DatabaseTrigger $trigger) {
            $trigger->on('sources');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::DELETE);
            $trigger->statement('
                UPDATE bots
                SET total_indexed_chunks_count = (
                    SELECT COALESCE(SUM(indexed_chunks_count), 0)
                    FROM sources
                    WHERE bot_id = OLD.bot_id
                ),
                updated_at = NOW()
                WHERE id = OLD.bot_id;
            ');
        });

        // Create trigger for sources count in bots table
        TriggerSchema::createTrigger('after_source_count_change', function (DatabaseTrigger $trigger) {
            $trigger->on('sources');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::INSERT);
            $trigger->statement('
                UPDATE bots
                SET sources_count = sources_count + 1,
                    updated_at = NOW()
                WHERE id = NEW.bot_id;
            ');
        });

        TriggerSchema::createTrigger('after_source_delete_count', function (DatabaseTrigger $trigger) {
            $trigger->on('sources');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::DELETE);
            $trigger->statement('
                UPDATE bots
                SET sources_count = sources_count - 1,
                    updated_at = NOW()
                WHERE id = OLD.bot_id;
            ');
        });
    }

    public function down(): void
    {
        // Drop triggers first
        TriggerSchema::dropTriggerIfExists('after_document_insert');
        TriggerSchema::dropTriggerIfExists('after_document_update');
        TriggerSchema::dropTriggerIfExists('after_document_delete');
        TriggerSchema::dropTriggerIfExists('after_source_insert');
        TriggerSchema::dropTriggerIfExists('after_source_update');
        TriggerSchema::dropTriggerIfExists('after_source_delete');
        TriggerSchema::dropTriggerIfExists('after_source_count_change');
        TriggerSchema::dropTriggerIfExists('after_source_delete_count');

        // Drop tables
        Schema::dropIfExists('documents');
        Schema::dropIfExists('sources');
    }
};
