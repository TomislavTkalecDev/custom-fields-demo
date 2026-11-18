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
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_record_id')->constrained('custom_records')->cascadeOnDelete();

            $table->string('value_string')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 20, 6)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->date('value_date')->nullable();
            $table->dateTime('value_datetime')->nullable();
            $table->foreignId('value_option_id')->nullable()
                ->constrained('custom_field_options')->nullOnDelete();
            $table->json('value_json')->nullable();

            $table->timestamps();

            $table->unique(['custom_field_id', 'custom_record_id']);
            $table->index('custom_record_id');
            $table->index(['custom_field_id', 'value_string']);
            $table->index(['custom_field_id', 'value_number']);
            $table->index(['custom_field_id', 'value_date']);
            $table->index(['custom_field_id', 'value_datetime']);
            $table->index(['custom_field_id', 'value_option_id']);
            $table->index(['custom_field_id', 'value_boolean']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
