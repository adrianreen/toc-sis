<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Policy extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'content',
        'policy_category_id',
        'scope',
        'programme_type',
        'status',
        'file_path',
        'file_name',
        'file_size',
        'created_by',
        'updated_by',
        'published_at',
        'version',
        'view_count',
        'download_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'file_size' => 'integer',
        'version' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the category that owns this policy
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PolicyCategory::class, 'policy_category_id');
    }

    /**
     * Get the user who created this policy
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this policy
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the assignments for this policy
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(PolicyAssignment::class);
    }

    /**
     * Get the views for this policy
     */
    public function views(): HasMany
    {
        return $this->hasMany(PolicyView::class);
    }

    /**
     * Scope to get published policies only
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get policies for a specific programme type
     */
    public function scopeForProgrammeType($query, $programmeType)
    {
        return $query->where(function ($q) use ($programmeType) {
            $q->where('programme_type', 'all')
              ->orWhere('programme_type', $programmeType);
        });
    }

    /**
     * Scope to get college-wide policies
     */
    public function scopeCollegeWide($query)
    {
        return $query->where('scope', 'college');
    }

    /**
     * Scope to get programme-specific policies
     */
    public function scopeProgrammeSpecific($query)
    {
        return $query->where('scope', 'programme');
    }

    /**
     * Check if policy is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * Check if policy has a file attachment
     */
    public function hasFile(): bool
    {
        return !empty($this->file_path) && Storage::disk('private')->exists($this->file_path);
    }

    /**
     * Get the file URL for download
     */
    public function getFileUrl(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return route('policies.download', $this);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHuman(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Increment download count
     */
    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    /**
     * Get programme type label
     */
    public function getProgrammeTypeLabel(): string
    {
        return match($this->programme_type) {
            'all' => 'All Programmes',
            'elc' => 'ELC Programmes',
            'degree_obu' => 'Degree (OBU) Programmes',
            'qqi' => 'QQI Programmes',
            default => ucfirst($this->programme_type),
        };
    }

    /**
     * Get scope label
     */
    public function getScopeLabel(): string
    {
        return match($this->scope) {
            'college' => 'College-wide',
            'programme' => 'Programme-specific',
            default => ucfirst($this->scope),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'published' => 'green',
            'draft' => 'yellow',
            'archived' => 'gray',
            default => 'gray',
        };
    }
}