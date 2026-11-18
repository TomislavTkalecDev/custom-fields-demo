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
        Schema::create('custom_field_value_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_value_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_record_id')->constrained('custom_records')->cascadeOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['custom_field_value_id', 'target_record_id'], 'cfvr_unique');
            $table->index('target_record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_value_relations');
    }
};
