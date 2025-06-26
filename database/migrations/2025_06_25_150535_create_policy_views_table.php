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
        Schema::create('policy_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['viewed', 'downloaded'])->default('viewed');
            $table->timestamp('viewed_at')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Index for analytics queries
            $table->index(['policy_id', 'action', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_views');
    }
};
