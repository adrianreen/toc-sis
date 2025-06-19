<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class QQICertification extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'qqi_certifications';

    protected $fillable = [
        'student_id',
        'programme_id',
        'module_id',
        'certification_batch_id',
        'certification_type',
        'qqi_award_type',
        'qqi_programme_code',
        'qqi_component_code',
        'completion_date',
        'final_grade_percentage',
        'grade_classification',
        'certification_status',
        'status_updated_date',
        'status_updated_by',
        'qbs_submission_data',
        'qbs_reference_number',
        'certificate_number',
        'certificate_issued_date',
        'delivery_method',
        'delivery_address',
        'delivery_date',
        'delivery_confirmed',
        'notes',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'final_grade_percentage' => 'decimal:2',
        'status_updated_date' => 'date',
        'qbs_submission_data' => 'array',
        'certificate_issued_date' => 'date',
        'delivery_date' => 'date',
        'delivery_confirmed' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'certification_status', 'certification_type', 'qqi_award_type',
                'final_grade_percentage', 'grade_classification', 'completion_date'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function certificationBatch(): BelongsTo
    {
        return $this->belongsTo(QQICertificationBatch::class);
    }

    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function certificationComponents(): HasMany
    {
        return $this->hasMany(QQICertificationComponent::class);
    }

    // Status management
    public function isPendingReview(): bool
    {
        return $this->certification_status === 'pending_review';
    }

    public function isReadyForSubmission(): bool
    {
        return $this->certification_status === 'ready_for_submission';
    }

    public function isSubmittedToQBS(): bool
    {
        return $this->certification_status === 'submitted_to_qbs';
    }

    public function isCertificateReceived(): bool
    {
        return $this->certification_status === 'certificate_received';
    }

    public function isCertificatePosted(): bool
    {
        return $this->certification_status === 'certificate_posted';
    }

    public function isCompleted(): bool
    {
        return $this->certification_status === 'completed';
    }

    public function isOnHold(): bool
    {
        return $this->certification_status === 'on_hold';
    }

    public function isRejected(): bool
    {
        return $this->certification_status === 'rejected';
    }

    // Status updates
    public function markReadyForSubmission(): void
    {
        $this->updateStatus('ready_for_submission');
    }

    public function markSubmittedToQBS(string $qbsReference = null, array $submissionData = []): void
    {
        $this->update([
            'certification_status' => 'submitted_to_qbs',
            'qbs_reference_number' => $qbsReference,
            'qbs_submission_data' => $submissionData,
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    public function markCertificateReceived(string $certificateNumber, \DateTime $issuedDate): void
    {
        $this->update([
            'certification_status' => 'certificate_received',
            'certificate_number' => $certificateNumber,
            'certificate_issued_date' => $issuedDate,
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    public function markCertificatePosted(string $deliveryMethod, string $deliveryAddress = null): void
    {
        $this->update([
            'certification_status' => 'certificate_posted',
            'delivery_method' => $deliveryMethod,
            'delivery_address' => $deliveryAddress,
            'delivery_date' => now(),
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    public function confirmDelivery(): void
    {
        $this->update([
            'delivery_confirmed' => true,
            'certification_status' => 'completed',
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    public function putOnHold(string $reason): void
    {
        $this->update([
            'certification_status' => 'on_hold',
            'notes' => ($this->notes ? $this->notes . "\n\n" : '') . 
                      "Put on hold on " . now()->format('Y-m-d') . ": " . $reason,
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'certification_status' => 'rejected',
            'notes' => ($this->notes ? $this->notes . "\n\n" : '') . 
                      "Rejected on " . now()->format('Y-m-d') . ": " . $reason,
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    protected function updateStatus(string $status): void
    {
        $this->update([
            'certification_status' => $status,
            'status_updated_date' => now(),
            'status_updated_by' => auth()->id(),
        ]);
    }

    // Certification data management
    public function getQQISubmissionData(): array
    {
        $data = [
            'student' => [
                'name' => $this->student->full_name,
                'student_number' => $this->student->student_number,
                'date_of_birth' => $this->student->date_of_birth?->format('Y-m-d'),
                'email' => $this->student->email,
            ],
            'certification' => [
                'type' => $this->certification_type,
                'award_type' => $this->qqi_award_type,
                'programme_code' => $this->qqi_programme_code,
                'component_code' => $this->qqi_component_code,
                'completion_date' => $this->completion_date->format('Y-m-d'),
                'final_grade' => $this->final_grade_percentage,
                'classification' => $this->grade_classification,
            ]
        ];

        // Add programme/module specific data
        if ($this->programme) {
            $data['programme'] = [
                'title' => $this->programme->title,
                'code' => $this->programme->code,
                'nfq_level' => $this->programme->nfq_level,
                'credit_value' => $this->programme->credit_value,
            ];
        }

        if ($this->module) {
            $data['module'] = [
                'title' => $this->module->title,
                'code' => $this->module->code,
                'credits' => $this->module->credits,
                'qqi_credit_value' => $this->module->qqi_credit_value,
            ];
        }

        // Add component breakdown if available
        if ($this->certificationComponents()->exists()) {
            $data['components'] = $this->certificationComponents->map(function ($component) {
                return [
                    'module_title' => $component->module->title,
                    'component_code' => $component->component_code,
                    'grade' => $component->component_grade,
                    'result' => $component->component_result,
                    'completion_date' => $component->component_completion_date->format('Y-m-d'),
                ];
            })->toArray();
        }

        return $data;
    }

    public function validateForSubmission(): array
    {
        $errors = [];

        // Required student data
        if (!$this->student) {
            $errors[] = 'No student associated';
        } elseif (!$this->student->date_of_birth) {
            $errors[] = 'Student date of birth missing';
        }

        // Required certification data
        if (!$this->qqi_award_type) {
            $errors[] = 'QQI award type missing';
        }

        if (!$this->completion_date) {
            $errors[] = 'Completion date missing';
        }

        if ($this->final_grade_percentage === null) {
            $errors[] = 'Final grade missing';
        }

        if (!$this->grade_classification) {
            $errors[] = 'Grade classification missing';
        }

        // Programme or module required
        if (!$this->programme_id && !$this->module_id) {
            $errors[] = 'Either programme or module must be specified';
        }

        // QQI codes required
        if ($this->certification_type === 'programme' && !$this->qqi_programme_code) {
            $errors[] = 'QQI programme code missing for programme certification';
        }

        if ($this->certification_type === 'standalone_module' && !$this->qqi_component_code) {
            $errors[] = 'QQI component code missing for module certification';
        }

        return $errors;
    }

    // Helper methods
    public function getDisplayTitle(): string
    {
        if ($this->programme) {
            return $this->programme->title;
        }
        
        if ($this->module) {
            return $this->module->title . ' (Standalone Module)';
        }
        
        return 'Unknown Certification';
    }

    public function getQQICode(): string
    {
        return $this->qqi_programme_code ?? $this->qqi_component_code ?? 'Unknown';
    }

    public function getDaysInCurrentStatus(): int
    {
        $statusDate = $this->status_updated_date ?? $this->created_at;
        return now()->diffInDays($statusDate);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('certification_status', $status);
    }

    public function scopeByAwardType($query, string $awardType)
    {
        return $query->where('qqi_award_type', $awardType);
    }

    public function scopeByCertificationType($query, string $type)
    {
        return $query->where('certification_type', $type);
    }

    public function scopeReadyForSubmission($query)
    {
        return $query->where('certification_status', 'ready_for_submission');
    }

    public function scopeInBatch($query, int $batchId)
    {
        return $query->where('certification_batch_id', $batchId);
    }

    public function scopeWithoutBatch($query)
    {
        return $query->whereNull('certification_batch_id');
    }

    public function scopeCompletedInYear($query, int $year)
    {
        return $query->whereYear('completion_date', $year);
    }

    public function scopeOverdue($query, int $days = 30)
    {
        return $query->whereIn('certification_status', ['pending_review', 'ready_for_submission'])
                    ->where('completion_date', '<', now()->subDays($days));
    }

    // Factory methods
    public static function createFromStudentModuleCompletion(Student $student, StudentModuleEnrolment $enrolment): self
    {
        $module = $enrolment->moduleInstance->module;
        $programme = $enrolment->programme;

        // Determine certification type
        $certificationType = $programme ? 'programme' : 'standalone_module';
        
        // Calculate final grade
        $finalGrade = $enrolment->calculateFinalGrade();
        
        return self::create([
            'student_id' => $student->id,
            'programme_id' => $programme?->id,
            'module_id' => $module->id,
            'certification_type' => $certificationType,
            'qqi_award_type' => $module->qqi_award_type ?? 'major',
            'qqi_programme_code' => $programme?->qqi_programme_code,
            'qqi_component_code' => $module->qqi_component_code,
            'completion_date' => $enrolment->completed_date ?? now(),
            'final_grade_percentage' => $finalGrade['final_grade'],
            'grade_classification' => $finalGrade['classification'] ?? 'Pass',
            'certification_status' => 'pending_review',
        ]);
    }
}