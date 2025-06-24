<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmailTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'subject',
        'category',
        'description',
        'body_html',
        'body_text',
        'available_variables',
        'is_active',
        'system_template',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
        'system_template' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'subject', 'category', 'is_active'])
            ->setDescriptionForEvent(fn (string $eventName) => "Email template {$eventName}")
            ->useLogName('email_templates');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSystemTemplates($query)
    {
        return $query->where('system_template', true);
    }

    public function scopeCustomTemplates($query)
    {
        return $query->where('system_template', false);
    }

    public function getUsageCountAttribute(): int
    {
        return $this->emailLogs()->count();
    }

    public function getLastUsedAttribute(): ?string
    {
        $lastLog = $this->emailLogs()->latest('sent_at')->first();

        return $lastLog?->sent_at?->diffForHumans();
    }

    public static function getAvailableVariables(): array
    {
        return [
            'student' => [
                'student.name' => 'Student full name',
                'student.first_name' => 'Student first name',
                'student.last_name' => 'Student last name',
                'student.email' => 'Student email address',
                'student.student_number' => 'Student number',
                'student.phone' => 'Student phone number',
                'student.status' => 'Student status',
            ],
            'programme' => [
                'programme.title' => 'Programme title',
                'programme.awarding_body' => 'Awarding body',
                'programme.nfq_level' => 'NFQ level',
                'programme.total_credits' => 'Total credits',
                'programme.description' => 'Programme description',
            ],
            'programme_instance' => [
                'programme_instance.label' => 'Programme instance label',
                'programme_instance.intake_start_date' => 'Intake start date',
                'programme_instance.intake_end_date' => 'Intake end date',
                'programme_instance.delivery_style' => 'Delivery style',
            ],
            'system' => [
                'college.name' => 'College name',
                'college.email' => 'College email',
                'college.phone' => 'College phone',
                'sender.name' => 'Sender name',
                'sender.email' => 'Sender email',
                'portal_url' => 'Student portal URL',
                'current_date' => 'Current date',
            ],
            'links' => [
                'transcript_link' => 'Transcript download link',
                'progress_link' => 'Progress view link',
                'profile_link' => 'Student profile link',
            ],
        ];
    }

    public function replaceVariables(Student $student, User $sender, array $customVariables = []): array
    {
        $variables = array_merge($this->getStudentVariables($student), [
            'college.name' => config('app.name', 'The Open College'),
            'college.email' => config('mail.from.address'),
            'college.phone' => '+353 1 234 5678',
            'sender.name' => $sender->name,
            'sender.email' => $sender->email,
            'portal_url' => url('/'),
            'current_date' => now()->format('d M Y'),
            'transcript_link' => route('transcripts.download', $student),
            'progress_link' => route('students.show-progress', $student),
            'profile_link' => route('students.show', $student),
        ], $customVariables);

        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyText = $this->body_text;

        foreach ($variables as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $subject = str_replace($placeholder, $value ?? '', $subject);
            $bodyHtml = str_replace($placeholder, $value ?? '', $bodyHtml);
            $bodyText = str_replace($placeholder, $value ?? '', $bodyText);
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'variables_used' => $variables,
        ];
    }

    private function getStudentVariables(Student $student): array
    {
        $variables = [
            'student.name' => $student->full_name,
            'student.first_name' => $student->first_name,
            'student.last_name' => $student->last_name,
            'student.email' => $student->email,
            'student.student_number' => $student->student_number,
            'student.phone' => $student->phone,
            'student.status' => ucfirst($student->status),
        ];

        // Add programme and programme instance variables if student has active programme enrolment
        $activeProgrammeEnrolment = $student->enrolments()
            ->where('status', 'active')
            ->where('enrolment_type', 'programme')
            ->with(['programmeInstance.programme'])
            ->first();

        if ($activeProgrammeEnrolment && $activeProgrammeEnrolment->programmeInstance) {
            $programmeInstance = $activeProgrammeEnrolment->programmeInstance;
            $programme = $programmeInstance->programme;

            // Programme variables
            $variables = array_merge($variables, [
                'programme.title' => $programme->title,
                'programme.awarding_body' => $programme->awarding_body,
                'programme.nfq_level' => $programme->nfq_level,
                'programme.total_credits' => $programme->total_credits,
                'programme.description' => $programme->description,
            ]);

            // Programme instance variables (replaces cohort)
            $variables = array_merge($variables, [
                'programme_instance.label' => $programmeInstance->label,
                'programme_instance.intake_start_date' => $programmeInstance->intake_start_date?->format('d M Y'),
                'programme_instance.intake_end_date' => $programmeInstance->intake_end_date?->format('d M Y'),
                'programme_instance.delivery_style' => ucfirst($programmeInstance->default_delivery_style),
            ]);
        }

        return $variables;
    }
}
