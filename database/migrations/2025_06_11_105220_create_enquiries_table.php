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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('enquiry_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('eircode')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->foreignId('programme_id')->constrained('programmes');
            $table->foreignId('prospective_cohort_id')->nullable()->constrained('cohorts');
            $table->enum('payment_status', ['pending', 'paid', 'deposit_paid', 'overdue'])->default('pending');
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('payment_due_date')->nullable();
            $table->enum('status', ['enquiry', 'application', 'accepted', 'converted', 'rejected', 'withdrawn'])->default('enquiry');
            $table->text('notes')->nullable();
            $table->boolean('microsoft_account_required')->default(false);
            $table->boolean('microsoft_account_created')->default(false);
            $table->foreignId('converted_student_id')->nullable()->constrained('students');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['status', 'payment_status']);
            $table->index(['programme_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
