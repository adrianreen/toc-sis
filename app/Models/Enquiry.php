<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enquiry extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'enquiry_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'county',
        'eircode',
        'date_of_birth',
        'programme_id',
        'prospective_cohort_id',
        'payment_status',
        'amount_due',
        'amount_paid',
        'payment_due_date',
        'status',
        'notes',
        'microsoft_account_required',
        'microsoft_account_created',
        'converted_student_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'payment_due_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'microsoft_account_required' => 'boolean',
        'microsoft_account_created' => 'boolean',
    ];

    public static function generateEnquiryNumber(): string
    {
        $year = date('Y');
        $lastEnquiry = self::whereYear('created_at', $year)
            ->orderBy('enquiry_number', 'desc')
            ->first();

        if ($lastEnquiry) {
            $lastNumber = intval(substr($lastEnquiry->enquiry_number, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'ENQ'.$year.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->amount_due - $this->amount_paid;
    }

    public function isPaymentComplete(): bool
    {
        return $this->payment_status === 'paid' || $this->amount_paid >= $this->amount_due;
    }

    public function canConvertToStudent(): bool
    {
        return $this->status === 'accepted' && ! $this->converted_student_id;
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function prospectiveProgrammeInstance(): BelongsTo
    {
        return $this->belongsTo(ProgrammeInstance::class, 'prospective_cohort_id');
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeUnconverted($query)
    {
        return $query->whereNull('converted_student_id');
    }
}
