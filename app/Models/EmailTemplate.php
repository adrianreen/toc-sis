<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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
            ->setDescriptionForEvent(fn(string $eventName) => "Email template {$eventName}")
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
                'programme.code' => 'Programme code',
                'programme.description' => 'Programme description',
            ],
            'cohort' => [
                'cohort.code' => 'Cohort code',
                'cohort.name' => 'Cohort name',
                'cohort.start_date' => 'Cohort start date',
                'cohort.end_date' => 'Cohort end date',
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
            'progress_link' => route('admin.student-progress', $student),
            'profile_link' => route('students.show', $student),
        ], $customVariables);

        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyText = $this->body_text;

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
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

        // Add programme and cohort variables if student has active enrolment
        $activeEnrolment = $student->enrolments()->where('status', 'active')->first();
        if ($activeEnrolment) {
            $variables = array_merge($variables, [
                'programme.title' => $activeEnrolment->programme->title,
                'programme.code' => $activeEnrolment->programme->code,
                'programme.description' => $activeEnrolment->programme->description,
            ]);

            if ($activeEnrolment->cohort) {
                $variables = array_merge($variables, [
                    'cohort.code' => $activeEnrolment->cohort->code,
                    'cohort.name' => $activeEnrolment->cohort->name,
                    'cohort.start_date' => $activeEnrolment->cohort->start_date?->format('d M Y'),
                    'cohort.end_date' => $activeEnrolment->cohort->end_date?->format('d M Y'),
                ]);
            }
        }

        return $variables;
    }
}