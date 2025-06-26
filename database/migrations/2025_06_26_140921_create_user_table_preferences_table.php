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
        Schema::create('user_table_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('table_name'); // e.g., 'students_index'
            $table->json('visible_columns'); // Array of column keys
            $table->json('column_order'); // Array of column keys in order
            $table->json('column_widths')->nullable(); // Optional column width settings
            $table->json('sort_preferences')->nullable(); // Default sort settings
            $table->timestamps();
            
            $table->unique(['user_id', 'table_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_table_preferences');
    }
};
