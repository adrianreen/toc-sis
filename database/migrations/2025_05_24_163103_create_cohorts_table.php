<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->string('code'); // 2501, 2504, 2509
            $table->string('name'); // January 2025, April 2025, etc.
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['planned', 'active', 'completed'])->default('planned');
            $table->timestamps();
            
            $table->unique(['programme_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};