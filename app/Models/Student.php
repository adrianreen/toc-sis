<?php

// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'email',
        'moodle_user_id',
        'phone',
        'address',
        'city',
        'county',
        'eircode',
        'date_of_birth',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // Generate next student number
    public static function generateStudentNumber(): string
    {
        $year = date('Y');
        $lastStudent = self::whereYear('created_at', $year)
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $lastNumber = intval(substr($lastStudent->student_number, -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $year.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function extensionRequests(): HasMany
    {
        return $this->hasMany(ExtensionRequest::class);
    }

    public function studentGradeRecords(): HasMany
    {
        return $this->hasMany(StudentGradeRecord::class);
    }

    public function gradeRecords(): HasMany
    {
        return $this->hasMany(StudentGradeRecord::class);
    }

    public function deferrals(): HasMany
    {
        return $this->hasMany(Deferral::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(Extension::class);
    }

    public function repeatAssessments(): HasMany
    {
        return $this->hasMany(RepeatAssessment::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function rplDocuments(): HasMany
    {
        return $this->hasMany(StudentDocument::class)
            ->where('document_type', 'rpl_proof');
    }

    public function getDocumentsByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return $this->documents()->where('document_type', $type)->get();
    }

    public function calculateOverallGradeForModule(ModuleInstance $moduleInstance): ?float
    {
        $gradeRecords = $this->studentGradeRecords()
            ->where('module_instance_id', $moduleInstance->id)
            ->whereNotNull('grade')
            ->get();

        if ($gradeRecords->isEmpty()) {
            return null;
        }

        $assessmentStrategy = $moduleInstance->module->assessment_strategy ?? [];
        if (empty($assessmentStrategy)) {
            return null;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($assessmentStrategy as $component) {
            $gradeRecord = $gradeRecords->firstWhere('assessment_component_name', $component['component_name']);

            if ($gradeRecord && $gradeRecord->percentage !== null) {
                $weight = $component['weighting'] ?? 0;
                $totalWeightedScore += ($gradeRecord->percentage * $weight);
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 1) : null;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'student_id');
    }

    // New architecture helper methods
    public function programmeEnrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class)->where('enrolment_type', 'programme');
    }

    public function moduleEnrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class)->where('enrolment_type', 'module');
    }

    public function getCurrentProgrammeEnrolments()
    {
        return $this->programmeEnrolments()->where('status', 'active')->with(['programmeInstance.programme']);
    }

    public function getCurrentModuleEnrolments()
    {
        return $this->moduleEnrolments()->where('status', 'active')->with(['moduleInstance.module', 'moduleInstance.tutor']);
    }

    public function getActiveModuleInstances()
    {
        // Get module instances from both programme and standalone enrolments
        $programmeModules = collect();
        $standaloneModules = collect();

        // From programme enrolments
        foreach ($this->getCurrentProgrammeEnrolments()->get() as $enrolment) {
            $programmeModules = $programmeModules->concat($enrolment->programmeInstance->moduleInstances);
        }

        // From standalone module enrolments
        foreach ($this->getCurrentModuleEnrolments()->get() as $enrolment) {
            $standaloneModules->push($enrolment->moduleInstance);
        }

        return $programmeModules->concat($standaloneModules)->unique('id');
    }

    public function getCurrentGradeRecords()
    {
        // Only return grade records for currently active module instances
        $activeModuleInstanceIds = $this->getActiveModuleInstances()->pluck('id')->toArray();

        return $this->studentGradeRecords()
            ->whereIn('module_instance_id', $activeModuleInstanceIds)
            ->where(function ($query) {
                $query->where('is_visible_to_student', true)
                    ->orWhere(function ($subQ) {
                        $subQ->whereNotNull('release_date')
                            ->where('release_date', '<=', now());
                    });
            });
    }

    public function hasActiveEnrollments(): bool
    {
        return $this->enrolments()
            ->where('status', 'active')
            ->exists();
    }
}
