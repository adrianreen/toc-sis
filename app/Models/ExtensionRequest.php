<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ExtensionRequest extends Model
{
    protected $fillable = [
        'student_id',
        'enrolment_id',
        'student_number',
        'contact_number',
        'extension_type',
        'course_name',
        'assignments_submitted',
        'course_commencement_date',
        'original_completion_date',
        'requested_completion_date',
        'additional_information',
        'medical_certificate_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'declaration_accepted',
        'extension_fee',
        'fee_paid',
        'fee_paid_at',
    ];

    protected $casts = [
        'course_commencement_date' => 'date',
        'original_completion_date' => 'date',
        'requested_completion_date' => 'date',
        'reviewed_at' => 'datetime',
        'fee_paid_at' => 'datetime',
        'declaration_accepted' => 'boolean',
        'fee_paid' => 'boolean',
        'extension_fee' => 'decimal:2',
        'assignments_submitted' => 'integer',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // Extension type helpers
    public function getExtensionTypeLabel(): string
    {
        return match($this->extension_type) {
            'two_weeks_free' => 'Two weeks (No additional fee)',
            'eight_weeks_minor' => '8 Weeks (Minor awards only) - €85.00',
            'twenty_four_weeks_major' => '24 Weeks (Major awards & bundles) - €165.00',
            'medical' => 'Medical extension (No additional fee)',
            default => 'Unknown'
        };
    }

    public function getExtensionDuration(): string
    {
        return match($this->extension_type) {
            'two_weeks_free' => '2 weeks',
            'eight_weeks_minor' => '8 weeks',
            'twenty_four_weeks_major' => '24 weeks',
            'medical' => 'As per medical certificate',
            default => 'Unknown'
        };
    }

    public function calculateExtensionFee(): float
    {
        return match($this->extension_type) {
            'eight_weeks_minor' => 85.00,
            'twenty_four_weeks_major' => 165.00,
            'two_weeks_free', 'medical' => 0.00,
            default => 0.00
        };
    }

    public function calculateRequestedCompletionDate(): ?Carbon
    {
        if (!$this->original_completion_date) {
            return null;
        }

        $originalDate = Carbon::parse($this->original_completion_date);

        return match($this->extension_type) {
            'two_weeks_free' => $originalDate->addWeeks(2),
            'eight_weeks_minor' => $originalDate->addWeeks(8),
            'twenty_four_weeks_major' => $originalDate->addWeeks(24),
            'medical' => null, // Manual calculation required based on medical cert
            default => null
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function requiresMedicalCertificate(): bool
    {
        return $this->extension_type === 'medical';
    }

    public function hasValidMedicalCertificate(): bool
    {
        return $this->requiresMedicalCertificate() 
            ? !empty($this->medical_certificate_path) 
            : true;
    }

    // Check if request is within 5 day window
    public function isWithinValidRequestWindow(): bool
    {
        if (!$this->original_completion_date) {
            return true; // Let validation handle this elsewhere
        }

        $deadline = Carbon::parse($this->original_completion_date);
        $requestDate = $this->created_at ?? now();
        
        return $requestDate->diffInDays($deadline) <= 5;
    }

    // Scope for filtering
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRequiringPayment($query)
    {
        return $query->where('extension_fee', '>', 0)->where('fee_paid', false);
    }
}
