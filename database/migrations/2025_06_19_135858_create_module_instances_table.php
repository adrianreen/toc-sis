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
        Schema::create('module_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('start_date');
            $table->date('target_end_date')->nullable();
            $table->enum('delivery_style', ['sync', 'async']);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_id', 'start_date']);
            $table->index('tutor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_instances');
    }
};
