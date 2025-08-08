<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE policy_views 
                MODIFY COLUMN action ENUM('viewed', 'downloaded', 'viewed_inline') DEFAULT 'viewed'
            ");
        } else {
            Log::info('Skipping enum column modification in policy_views: unsupported on ' . DB::getDriverName());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE policy_views 
                MODIFY COLUMN action ENUM('viewed', 'downloaded') DEFAULT 'viewed'
            ");
        } else {
            Log::info('Skipping enum rollback in policy_views: unsupported on ' . DB::getDriverName());
        }
    }
};
