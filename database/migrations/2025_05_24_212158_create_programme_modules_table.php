<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->integer('sequence')->default(1); // Order of modules in programme
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
            
            $table->unique(['programme_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_modules');
    }
};