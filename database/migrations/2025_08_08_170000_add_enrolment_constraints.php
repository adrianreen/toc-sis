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
            // Drop the constraint if it exists
            try {
                DB::statement('ALTER TABLE enrolments DROP CHECK chk_enrolment_mutual_exclusivity');
            } catch (\Throwable $e) {
                // Probably doesn't exist — ignore
            }

            // Add new constraint
            DB::statement("
                ALTER TABLE enrolments 
                ADD CONSTRAINT chk_enrolment_mutual_exclusivity 
                CHECK (
                    (enrolment_type = 'programme' AND programme_instance_id IS NOT NULL AND module_instance_id IS NULL) OR
                    (enrolment_type = 'module' AND module_instance_id IS NOT NULL AND programme_instance_id IS NULL)
                )
            ");


        } else {
            Log::info('Skipping MySQL-only constraints and indexes in enrolments migration: driver is ' . DB::getDriverName());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Drop indexes
            DB::statement('DROP INDEX idx_active_programme_enrolments ON enrolments');
            DB::statement('DROP INDEX idx_active_module_enrolments ON enrolments');

            // Drop constraint
            try {
                DB::statement('ALTER TABLE enrolments DROP CHECK chk_enrolment_mutual_exclusivity');
            } catch (\Throwable $e) {
                // Ignore if it doesn't exist
            }
        } else {
            Log::info('Skipping MySQL-only rollback in enrolments migration: driver is ' . DB::getDriverName());
        }
    }
};
