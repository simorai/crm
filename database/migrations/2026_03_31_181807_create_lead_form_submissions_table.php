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
        Schema::create('lead_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->json('data')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('origin')->nullable();
            $table->boolean('processed')->default(false);
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_form_submissions');
    }
};
