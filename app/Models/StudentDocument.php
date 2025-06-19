<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentDocument extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_id',
        'profile_extension_id',
        'document_type',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'is_verified',
        'verified_date',
        'verified_by',
        'expiry_date',
        'verification_notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['document_type', 'is_verified', 'verified_date', 'expiry_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function profileExtension(): BelongsTo
    {
        return $this->belongsTo(StudentProfileExtension::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // File management
    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPDF(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function verify(string $notes = null): void
    {
        $this->update([
            'is_verified' => true,
            'verified_date' => now(),
            'verified_by' => auth()->id(),
            'verification_notes' => $notes,
        ]);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}