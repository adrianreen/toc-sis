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
        Schema::create('programme_instance_curriculum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_instance_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_instance_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['programme_instance_id', 'module_instance_id'], 'programme_module_unique');
            $table->index('programme_instance_id');
            $table->index('module_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_instance_curriculum');
    }
};
