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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('module_code')->unique();
            $table->integer('credit_value');
            $table->json('assessment_strategy');
            $table->boolean('allows_standalone_enrolment')->default(false);
            $table->enum('async_instance_cadence', ['monthly', 'quarterly', 'bi_annually', 'annually'])->default('quarterly');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('module_code');
            $table->index('allows_standalone_enrolment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
