<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'viewed_inline'
        DB::statement("ALTER TABLE policy_views MODIFY COLUMN action ENUM('viewed', 'downloaded', 'viewed_inline') DEFAULT 'viewed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum (this will fail if there are 'viewed_inline' records)
        DB::statement("ALTER TABLE policy_views MODIFY COLUMN action ENUM('viewed', 'downloaded') DEFAULT 'viewed'");
    }
};
