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
        Schema::create('programme_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->date('intake_start_date');
            $table->date('intake_end_date')->nullable();
            $table->enum('default_delivery_style', ['sync', 'async']);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['programme_id', 'intake_start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_instances');
    }
};
