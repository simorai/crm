<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('vat', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 20)->default('prospect');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('name');
            $table->index('status');
            $table->index('vat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
