<?php
// app/Models/RepeatAssessment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RepeatAssessment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_grade_record_id',
        'student_id',
        'module_instance_id',
        'reason',
        'repeat_due_date',
        'cap_grade',
        'status',
        'approved_by',
        'approved_at',
        // Payment tracking
        'payment_status',
        'payment_method',
        'payment_amount',
        'payment_date',
        'payment_notes',
        // Notification tracking
        'notification_sent',
        'notification_date',
        'notification_method',
        'notification_notes',
        // Moodle integration
        'moodle_setup_status',
        'moodle_setup_date',
        'moodle_course_id',
        'moodle_notes',
        // Workflow management
        'workflow_stage',
        'deadline_date',
        'priority_level',
        'staff_notes',
        // Student communication
        'student_response',
        'student_response_date',
        // Assignment and tracking
        'assigned_to',
        'last_contact_date',
        'contact_history',
    ];

    protected $casts = [
        'repeat_due_date' => 'date',
        'cap_grade' => 'decimal:2',
        'approved_at' => 'datetime',
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'notification_sent' => 'boolean',
        'notification_date' => 'datetime',
        'moodle_setup_date' => 'datetime',
        'deadline_date' => 'date',
        'student_response_date' => 'datetime',
        'last_contact_date' => 'datetime',
        'contact_history' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 'approved_by', 'approved_at',
                'payment_status', 'payment_method', 'payment_amount', 'payment_date',
                'notification_sent', 'notification_date', 'notification_method',
                'moodle_setup_status', 'moodle_setup_date', 'moodle_course_id',
                'workflow_stage', 'deadline_date', 'priority_level',
                'assigned_to', 'last_contact_date'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function studentGradeRecord(): BelongsTo
    {
        return $this->belongsTo(StudentGradeRecord::class);
    }

    public function getAssessmentComponentNameAttribute(): string
    {
        return $this->studentGradeRecord->assessment_component_name ?? 'Unknown Assessment';
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function moduleInstance(): BelongsTo
    {
        return $this->belongsTo(ModuleInstance::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Repeat assessment approved');
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'workflow_stage' => 'cancelled',
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['reason' => $reason])
            ->log('Repeat assessment rejected');
    }

    // Payment methods
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPaymentPending(): bool
    {
        return in_array($this->payment_status, ['pending', 'overdue']);
    }

    public function isPaymentWaived(): bool
    {
        return $this->payment_status === 'waived';
    }

    public function markAsPaid(string $method, float $amount, string $notes = null): void
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'payment_amount' => $amount,
            'payment_date' => now(),
            'payment_notes' => $notes,
        ]);

        $this->progressWorkflow();

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties([
                'method' => $method,
                'amount' => $amount,
                'notes' => $notes
            ])
            ->log('Payment recorded for repeat assessment');
    }

    public function waivePayment(string $reason = null): void
    {
        $this->update([
            'payment_status' => 'waived',
            'payment_method' => 'waived',
            'payment_date' => now(),
            'payment_notes' => $reason,
        ]);

        $this->progressWorkflow();

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['reason' => $reason])
            ->log('Payment waived for repeat assessment');
    }

    // Notification methods
    public function isNotificationSent(): bool
    {
        return $this->notification_sent;
    }

    public function markNotificationSent(string $method, string $notes = null): void
    {
        $this->update([
            'notification_sent' => true,
            'notification_date' => now(),
            'notification_method' => $method,
            'notification_notes' => $notes,
            'last_contact_date' => now(),
        ]);

        $this->addContactHistory('notification_sent', $method, $notes);
        $this->progressWorkflow();

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['method' => $method, 'notes' => $notes])
            ->log('Student notification sent for repeat assessment');
    }

    // Moodle integration methods
    public function isMoodleSetupRequired(): bool
    {
        return $this->moodle_setup_status !== 'not_required';
    }

    public function isMoodleSetupComplete(): bool
    {
        return $this->moodle_setup_status === 'completed';
    }

    public function markMoodleSetupComplete(string $courseId, string $notes = null): void
    {
        $this->update([
            'moodle_setup_status' => 'completed',
            'moodle_setup_date' => now(),
            'moodle_course_id' => $courseId,
            'moodle_notes' => $notes,
        ]);

        $this->progressWorkflow();

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['course_id' => $courseId, 'notes' => $notes])
            ->log('Moodle setup completed for repeat assessment');
    }

    public function markMoodleSetupFailed(string $reason): void
    {
        $this->update([
            'moodle_setup_status' => 'failed',
            'moodle_notes' => $reason,
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['reason' => $reason])
            ->log('Moodle setup failed for repeat assessment');
    }

    // Workflow management
    public function progressWorkflow(): void
    {
        switch ($this->workflow_stage) {
            case 'identified':
                if ($this->notification_sent) {
                    $this->workflow_stage = 'notified';
                }
                break;
            case 'notified':
                if ($this->isPaid() || $this->isPaymentWaived()) {
                    $this->workflow_stage = $this->isMoodleSetupRequired() ? 'moodle_setup' : 'active';
                } elseif ($this->isPaymentPending()) {
                    $this->workflow_stage = 'payment_pending';
                }
                break;
            case 'payment_pending':
                if ($this->isPaid() || $this->isPaymentWaived()) {
                    $this->workflow_stage = $this->isMoodleSetupRequired() ? 'moodle_setup' : 'active';
                }
                break;
            case 'moodle_setup':
                if ($this->isMoodleSetupComplete() || !$this->isMoodleSetupRequired()) {
                    $this->workflow_stage = 'active';
                }
                break;
        }

        $this->save();
    }

    public function canProgress(): bool
    {
        return match ($this->workflow_stage) {
            'identified' => !$this->notification_sent,
            'notified' => $this->isPaymentPending(),
            'payment_pending' => true,
            'moodle_setup' => !$this->isMoodleSetupComplete(),
            'active' => false,
            'completed' => false,
            'cancelled' => false,
            default => false,
        };
    }

    // Contact and communication
    public function addContactHistory(string $type, string $method, string $notes = null): void
    {
        $history = $this->contact_history ?? [];
        $history[] = [
            'type' => $type,
            'method' => $method,
            'notes' => $notes,
            'date' => now()->toISOString(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'System',
        ];

        $this->update([
            'contact_history' => $history,
            'last_contact_date' => now(),
        ]);
    }

    public function recordStudentResponse(string $response): void
    {
        $this->update([
            'student_response' => $response,
            'student_response_date' => now(),
        ]);

        $this->addContactHistory('student_response', 'system', $response);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['response' => $response])
            ->log('Student response recorded for repeat assessment');
    }

    // Priority and deadline management
    public function isOverdue(): bool
    {
        return $this->deadline_date && $this->deadline_date->isPast() && !in_array($this->workflow_stage, ['completed', 'cancelled']);
    }

    public function isDueSoon(int $days = 7): bool
    {
        return $this->deadline_date && $this->deadline_date->diffInDays(now()) <= $days && !$this->isOverdue();
    }

    public function escalatePriority(): void
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $currentIndex = array_search($this->priority_level, $priorities);
        
        if ($currentIndex !== false && $currentIndex < count($priorities) - 1) {
            $this->update(['priority_level' => $priorities[$currentIndex + 1]]);
            
            activity()
                ->performedOn($this)
                ->causedBy(auth()->user())
                ->log('Priority escalated for repeat assessment');
        }
    }

    // Scopes for filtering
    public function scopeByWorkflowStage($query, string $stage)
    {
        return $query->where('workflow_stage', $stage);
    }

    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopePendingPayment($query)
    {
        return $query->whereIn('payment_status', ['pending', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline_date', '<', now())
                     ->whereNotIn('workflow_stage', ['completed', 'cancelled']);
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->whereBetween('deadline_date', [now(), now()->addDays($days)])
                     ->whereNotIn('workflow_stage', ['completed', 'cancelled']);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority_level', $priority);
    }
}