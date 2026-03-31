<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Polymorphic: the subject being logged (Deal, Entity, Person, CalendarEvent, etc.)
            $table->morphs('loggable');
            $table->string('type');             // e.g. 'note', 'stage_change', 'email_sent', 'created', 'updated'
            $table->text('description');
            $table->json('metadata')->nullable(); // extra data (old_stage, new_stage, file_path, etc.)
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
