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
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('awarding_body');
            $table->integer('nfq_level');
            $table->integer('total_credits');
            $table->text('description')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['awarding_body', 'nfq_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
