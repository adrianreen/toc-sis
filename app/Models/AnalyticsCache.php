<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsCache extends Model
{
    use HasFactory;

    protected $table = 'analytics_cache';

    protected $fillable = [
        'cache_key',
        'cache_data',
        'expires_at',
    ];

    protected $casts = [
        'cache_data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Get cached data by key if not expired
     */
    public static function getCached($key)
    {
        $cache = self::where('cache_key', $key)
            ->where('expires_at', '>', now())
            ->first();

        return $cache ? $cache->cache_data : null;
    }

    /**
     * Store data in cache with expiration
     */
    public static function setCached($key, $data, $expiresInMinutes = 60)
    {
        try {
            return \DB::transaction(function () use ($key, $data, $expiresInMinutes) {
                return self::updateOrCreate(
                    ['cache_key' => $key],
                    [
                        'cache_data' => $data,
                        'expires_at' => now()->addMinutes($expiresInMinutes),
                    ]
                );
            }, 3); // Retry up to 3 times on deadlock
        } catch (\Exception $e) {
            \Log::warning('Analytics cache write failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            // If cache write fails, continue without caching
            // This prevents 500 errors on rapid refreshes
            return null;
        }
    }

    /**
     * Clear expired cache entries
     */
    public static function clearExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Clear cache for specific key
     */
    public static function clearKey($key)
    {
        return self::where('cache_key', $key)->delete();
    }

    /**
     * Clear all cache
     */
    public static function clearAll()
    {
        return self::truncate();
    }
}
