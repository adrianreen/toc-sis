<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add check constraints to ensure enrolment mutual exclusivity
        // First drop if exists to handle retry scenarios (MySQL syntax)
        try {
            DB::statement("ALTER TABLE enrolments DROP CHECK chk_enrolment_mutual_exclusivity");
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }
        
        DB::statement("
            ALTER TABLE enrolments 
            ADD CONSTRAINT chk_enrolment_mutual_exclusivity 
            CHECK (
                (enrolment_type = 'programme' AND programme_instance_id IS NOT NULL AND module_instance_id IS NULL) OR
                (enrolment_type = 'module' AND module_instance_id IS NOT NULL AND programme_instance_id IS NULL)
            )
        ");

        // Add indexes for performance (MySQL doesn't support partial unique indexes)
        // Uniqueness will be enforced at application level via EnrolmentService
        DB::statement("
            CREATE INDEX idx_active_programme_enrolments 
            ON enrolments (student_id, programme_instance_id, enrolment_type, status, deleted_at)
        ");

        DB::statement("
            CREATE INDEX idx_active_module_enrolments 
            ON enrolments (student_id, module_instance_id, enrolment_type, status, deleted_at)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes
        DB::statement("DROP INDEX IF EXISTS idx_active_programme_enrolments");
        DB::statement("DROP INDEX IF EXISTS idx_active_module_enrolments");
        
        // Remove check constraint (MySQL syntax)
        try {
            DB::statement("ALTER TABLE enrolments DROP CHECK chk_enrolment_mutual_exclusivity");
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }
    }
};
