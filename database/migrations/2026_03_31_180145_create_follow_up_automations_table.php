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
        Schema::create('follow_up_automations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('deal_id')->index();
            $table->unsignedBigInteger('email_template_id')->nullable();
            $table->string('status')->default('active'); // active, cancelled, completed
            $table->unsignedTinyInteger('template_index')->default(0);
            $table->unsignedTinyInteger('emails_sent')->default(0);
            $table->timestamp('next_send_at')->nullable()->index();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
            $table->foreign('email_template_id')->references('id')->on('email_templates')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_automations');
    }
};
