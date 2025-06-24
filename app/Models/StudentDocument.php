<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentDocument extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'student_id',
        'document_type',
        'title',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_hash',
        'status',
        'description',
        'rejection_reason',
        'metadata',
        'uploaded_by',
        'uploaded_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    // Activity logging
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Document type labels
    public static function getDocumentTypeLabels(): array
    {
        return [
            'rpl_proof' => 'Prior Learning Documentation',
            'transcript' => 'Academic Transcript',
            'certificate' => 'Certificate/Diploma',
            'identity_document' => 'Identity Document',
            'qualification_certificate' => 'Qualification Certificate',
            'other' => 'Other Document',
        ];
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return self::getDocumentTypeLabels()[$this->document_type] ?? 'Unknown';
    }

    // File size formatting
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    // Status badge color helper
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'uploaded' => 'bg-blue-100 text-blue-800',
            'verified' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'archived' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // File operations
    public function getDownloadUrl(): string
    {
        return route('student-documents.download', $this);
    }

    public function fileExists(): bool
    {
        return Storage::disk('student_documents')->exists($this->file_path);
    }

    // Scopes
    public function scopeForDocumentType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
