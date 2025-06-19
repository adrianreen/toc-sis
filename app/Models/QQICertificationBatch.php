<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class QQICertificationBatch extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'qqi_certification_batches';

    protected $fillable = [
        'batch_name',
        'submission_deadline',
        'qbs_submission_date',
        'certificates_received_date',
        'certificates_posted_date',
        'status',
        'total_students',
        'processed_students',
        'notes',
        'batch_metadata',
        'created_by',
        'submitted_by',
    ];

    protected $casts = [
        'submission_deadline' => 'date',
        'qbs_submission_date' => 'date',
        'certificates_received_date' => 'date',
        'certificates_posted_date' => 'date',
        'batch_metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'batch_name', 'status', 'submission_deadline', 'qbs_submission_date',
                'total_students', 'processed_students'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function certifications(): HasMany
    {
        return $this->hasMany(QQICertification::class, 'certification_batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // Status management
    public function isInPreparation(): bool
    {
        return $this->status === 'preparation';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted_to_qbs';
    }

    public function areCertificatesReceived(): bool
    {
        return $this->status === 'certificates_received';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canAddCertifications(): bool
    {
        return $this->isInPreparation();
    }

    public function canSubmitToQBS(): bool
    {
        return $this->isInPreparation() && 
               $this->certifications()->where('certification_status', 'ready_for_submission')->exists();
    }

    // Batch operations
    public function submitToQBS(): bool
    {
        if (!$this->canSubmitToQBS()) {
            return false;
        }

        \DB::transaction(function () {
            // Update batch status
            $this->update([
                'status' => 'submitted_to_qbs',
                'qbs_submission_date' => now(),
                'submitted_by' => auth()->id(),
            ]);

            // Update certification statuses
            $this->certifications()
                 ->where('certification_status', 'ready_for_submission')
                 ->update(['certification_status' => 'submitted_to_qbs']);

            // Log activity
            activity()
                ->performedOn($this)
                ->causedBy(auth()->user())
                ->withProperties([
                    'certification_count' => $this->certifications()->count(),
                    'submission_date' => now()->toDateString()
                ])
                ->log('QQI certification batch submitted to QBS');
        });

        return true;
    }

    public function markCertificatesReceived(): bool
    {
        if (!$this->isSubmitted()) {
            return false;
        }

        $this->update([
            'status' => 'certificates_received',
            'certificates_received_date' => now(),
        ]);

        // Update individual certification statuses
        $this->certifications()
             ->where('certification_status', 'submitted_to_qbs')
             ->update(['certification_status' => 'certificate_received']);

        return true;
    }

    public function markCertificatesPosted(): bool
    {
        if (!$this->areCertificatesReceived()) {
            return false;
        }

        $this->update([
            'status' => 'certificates_posted',
            'certificates_posted_date' => now(),
        ]);

        return true;
    }

    public function markCompleted(): bool
    {
        $this->update(['status' => 'completed']);
        
        // Update any remaining certifications
        $this->certifications()
             ->whereIn('certification_status', ['certificate_received', 'certificate_posted'])
             ->update(['certification_status' => 'completed']);

        return true;
    }

    // Statistics and reporting
    public function getProgressStatistics(): array
    {
        $certifications = $this->certifications();
        
        return [
            'total' => $certifications->count(),
            'ready_for_submission' => $certifications->where('certification_status', 'ready_for_submission')->count(),
            'submitted' => $certifications->where('certification_status', 'submitted_to_qbs')->count(),
            'received' => $certifications->where('certification_status', 'certificate_received')->count(),
            'posted' => $certifications->where('certification_status', 'certificate_posted')->count(),
            'completed' => $certifications->where('certification_status', 'completed')->count(),
        ];
    }

    public function getSubmissionSummary(): array
    {
        $certifications = $this->certifications()->with(['student', 'programme', 'module'])->get();
        
        $summary = [
            'programmes' => [],
            'award_types' => [],
            'total_students' => $certifications->count(),
        ];
        
        foreach ($certifications as $cert) {
            // Programme breakdown
            $programmeKey = $cert->programme ? $cert->programme->title : 'Standalone Modules';
            if (!isset($summary['programmes'][$programmeKey])) {
                $summary['programmes'][$programmeKey] = 0;
            }
            $summary['programmes'][$programmeKey]++;
            
            // Award type breakdown
            $awardType = $cert->qqi_award_type;
            if (!isset($summary['award_types'][$awardType])) {
                $summary['award_types'][$awardType] = 0;
            }
            $summary['award_types'][$awardType]++;
        }
        
        return $summary;
    }

    public function updateStudentCounts(): void
    {
        $total = $this->certifications()->count();
        $processed = $this->certifications()
                          ->whereIn('certification_status', ['completed', 'certificate_posted'])
                          ->count();
        
        $this->update([
            'total_students' => $total,
            'processed_students' => $processed,
        ]);
    }

    // Validation
    public function validateForSubmission(): array
    {
        $errors = [];
        
        if ($this->certifications()->count() === 0) {
            $errors[] = 'No certifications in batch';
        }
        
        $invalidCertifications = $this->certifications()
            ->where('certification_status', '!=', 'ready_for_submission')
            ->count();
            
        if ($invalidCertifications > 0) {
            $errors[] = "Some certifications are not ready for submission ({$invalidCertifications} items)";
        }
        
        if ($this->submission_deadline && now() > $this->submission_deadline) {
            $errors[] = 'Submission deadline has passed';
        }
        
        return $errors;
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInPreparation($query)
    {
        return $query->where('status', 'preparation');
    }

    public function scopeOverdue($query)
    {
        return $query->where('submission_deadline', '<', now())
                    ->where('status', 'preparation');
    }

    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('created_at', $year);
    }

    // Factory methods
    public static function createQuarterlyBatch(int $year, int $quarter): self
    {
        $quarterNames = [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4'];
        $quarterDeadlines = [
            1 => "{$year}-03-31",
            2 => "{$year}-06-30", 
            3 => "{$year}-09-30",
            4 => "{$year}-12-31"
        ];
        
        return self::create([
            'batch_name' => "{$year} {$quarterNames[$quarter]} QQI Certification Batch",
            'submission_deadline' => $quarterDeadlines[$quarter],
            'status' => 'preparation',
            'created_by' => auth()->id(),
            'batch_metadata' => [
                'year' => $year,
                'quarter' => $quarter,
                'auto_generated' => true,
            ]
        ]);
    }

    public static function getOrCreateCurrentBatch(): self
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);
        
        $existingBatch = self::where('status', 'preparation')
                            ->whereYear('created_at', $currentYear)
                            ->whereRaw('JSON_EXTRACT(batch_metadata, "$.quarter") = ?', [$currentQuarter])
                            ->first();
        
        if ($existingBatch) {
            return $existingBatch;
        }
        
        return self::createQuarterlyBatch($currentYear, $currentQuarter);
    }
}