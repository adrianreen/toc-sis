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
        Schema::create('extension_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrolment_id')->constrained()->onDelete('cascade');
            $table->string('student_number'); // For form display/validation
            $table->string('contact_number');
            
            // Extension options from form
            $table->enum('extension_type', [
                'two_weeks_free',      // Option 1: Two weeks (minor & major awards) - No Additional Fees
                'eight_weeks_minor',   // Option 2: 8 Weeks (minor awards only) - €85.00 fee
                'twenty_four_weeks_major', // Option 3: 24 Weeks (major awards & bundle courses only) - €165.00 fee
                'medical'              // Option 4: Medical (no additional fee)
            ]);
            
            // Course and background details
            $table->string('course_name');
            $table->integer('assignments_submitted')->default(0);
            $table->date('course_commencement_date');
            $table->date('original_completion_date');
            $table->date('requested_completion_date')->nullable(); // Calculated based on extension type
            
            // Additional information
            $table->text('additional_information');
            $table->string('medical_certificate_path')->nullable(); // File upload for medical extensions
            
            // Workflow status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->boolean('declaration_accepted')->default(true);
            
            // Financial tracking
            $table->decimal('extension_fee', 8, 2)->default(0.00);
            $table->boolean('fee_paid')->default(false);
            $table->timestamp('fee_paid_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['student_id', 'status']);
            $table->index(['enrolment_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_requests');
    }
};
