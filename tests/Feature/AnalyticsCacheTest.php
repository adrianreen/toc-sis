<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AnalyticsCache;
use App\Models\AnalyticsMetric;
use App\Models\User;
use App\Models\Student;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AnalyticsCacheTest extends TestCase
{
    use RefreshDatabase;

    protected $analyticsService;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = app(AnalyticsService::class);
        $this->manager = User::factory()->create(['role' => 'manager']);
    }

    public function test_cache_creation_and_retrieval()
    {
        // Clear any existing cache
        AnalyticsCache::clearAll();
        
        // Verify cache is empty
        $this->assertEquals(0, AnalyticsCache::count());

        // First call should create cache entry
        $result1 = $this->analyticsService->getSystemOverview();
        
        // Verify cache entry was created
        $this->assertEquals(1, AnalyticsCache::where('cache_key', 'system_overview')->count());
        
        // Second call should use cached data
        $result2 = $this->analyticsService->getSystemOverview();
        
        // Results should be identical
        $this->assertEquals($result1, $result2);
        
        // Should still only have one cache entry
        $this->assertEquals(1, AnalyticsCache::where('cache_key', 'system_overview')->count());
    }

    public function test_cache_expiration_functionality()
    {
        // Create expired cache entry
        AnalyticsCache::create([
            'cache_key' => 'test_expired_cache',
            'cache_data' => json_encode(['test' => 'expired_data']),
            'expires_at' => Carbon::now()->subHour()
        ]);

        // Create valid cache entry
        AnalyticsCache::create([
            'cache_key' => 'test_valid_cache',
            'cache_data' => json_encode(['test' => 'valid_data']),
            'expires_at' => Carbon::now()->addHour()
        ]);

        // Expired cache should return null
        $expiredData = AnalyticsCache::getCached('test_expired_cache');
        $this->assertNull($expiredData);

        // Valid cache should return data
        $validData = AnalyticsCache::getCached('test_valid_cache');
        $this->assertNotNull($validData);
        $this->assertEquals(['test' => 'valid_data'], $validData);
    }

    public function test_cache_performance_improvement()
    {
        // Create test data to make queries take some time
        Student::factory()->count(50)->create();
        
        AnalyticsCache::clearAll();

        // Measure first call (cache miss)
        $startTime = microtime(true);
        $result1 = $this->analyticsService->getSystemOverview();
        $endTime = microtime(true);
        $firstCallTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Measure second call (cache hit)
        $startTime = microtime(true);
        $result2 = $this->analyticsService->getSystemOverview();
        $endTime = microtime(true);
        $secondCallTime = ($endTime - $startTime) * 1000;

        // Cache hit should be significantly faster
        $this->assertLessThan($firstCallTime, $secondCallTime);
        
        // Cache hit should be very fast (less than 10ms)
        $this->assertLessThan(10, $secondCallTime);
        
        // Data should be identical
        $this->assertEquals($result1, $result2);
    }

    public function test_cache_refresh_functionality()
    {
        // Create initial cache
        $result1 = $this->analyticsService->getSystemOverview();
        
        $initialCacheEntry = AnalyticsCache::where('cache_key', 'system_overview')->first();
        $initialTimestamp = $initialCacheEntry->updated_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        // Refresh cache
        $this->analyticsService->refreshAllCache();
        
        $refreshedCacheEntry = AnalyticsCache::where('cache_key', 'system_overview')->first();
        $refreshedTimestamp = $refreshedCacheEntry->updated_at;

        // Cache should have been updated
        $this->assertTrue($refreshedTimestamp->gt($initialTimestamp));
        
        // Should still have valid cache data
        $result2 = $this->analyticsService->getSystemOverview();
        $this->assertNotNull($result2);
        $this->assertArrayHasKey('students', $result2);
    }

    public function test_expired_cache_cleanup()
    {
        // Create multiple cache entries with different expiration times
        AnalyticsCache::create([
            'cache_key' => 'expired_1',
            'cache_data' => json_encode(['test' => 'data1']),
            'expires_at' => Carbon::now()->subHours(2)
        ]);

        AnalyticsCache::create([
            'cache_key' => 'expired_2', 
            'cache_data' => json_encode(['test' => 'data2']),
            'expires_at' => Carbon::now()->subHour()
        ]);

        AnalyticsCache::create([
            'cache_key' => 'valid_1',
            'cache_data' => json_encode(['test' => 'data3']),
            'expires_at' => Carbon::now()->addHour()
        ]);

        AnalyticsCache::create([
            'cache_key' => 'valid_2',
            'cache_data' => json_encode(['test' => 'data4']),
            'expires_at' => Carbon::now()->addHours(2)
        ]);

        // Verify initial count
        $this->assertEquals(4, AnalyticsCache::count());

        // Clear expired cache
        $clearedCount = $this->analyticsService->clearExpiredCache();
        
        // Should have cleared 2 expired entries
        $this->assertEquals(2, $clearedCount);
        
        // Should have 2 valid entries remaining
        $this->assertEquals(2, AnalyticsCache::count());
        
        // Valid entries should still be accessible
        $this->assertNotNull(AnalyticsCache::getCached('valid_1'));
        $this->assertNotNull(AnalyticsCache::getCached('valid_2'));
        
        // Expired entries should be gone
        $this->assertNull(AnalyticsCache::getCached('expired_1'));
        $this->assertNull(AnalyticsCache::getCached('expired_2'));
    }

    public function test_cache_data_consistency_after_data_changes()
    {
        // Get initial data
        $initialOverview = $this->analyticsService->getSystemOverview();
        $initialStudentCount = $initialOverview['students']['total'];

        // Add new student
        Student::factory()->create(['status' => 'active']);

        // Cache should return old data (not refreshed yet)
        $cachedOverview = $this->analyticsService->getSystemOverview();
        $this->assertEquals($initialStudentCount, $cachedOverview['students']['total']);

        // After cache refresh, should show updated data
        $this->analyticsService->refreshAllCache();
        $refreshedOverview = $this->analyticsService->getSystemOverview();
        $this->assertEquals($initialStudentCount + 1, $refreshedOverview['students']['total']);
    }

    public function test_multiple_cache_keys_managed_independently()
    {
        AnalyticsCache::clearAll();

        // Generate different analytics that use different cache keys
        $systemOverview = $this->analyticsService->getSystemOverview();
        $studentPerformance = $this->analyticsService->getStudentPerformanceTrends();
        $programmeEffectiveness = $this->analyticsService->getProgrammeEffectiveness();

        // Should have created multiple cache entries
        $this->assertGreaterThanOrEqual(3, AnalyticsCache::count());

        // Each should have different cache keys
        $cacheKeys = AnalyticsCache::pluck('cache_key')->toArray();
        $this->assertContains('system_overview', $cacheKeys);
        $this->assertContains('student_performance_trends_monthly_12', $cacheKeys);
        $this->assertContains('programme_effectiveness', $cacheKeys);

        // Clear specific cache key
        AnalyticsCache::clearKey('system_overview');
        
        // System overview cache should be gone
        $this->assertNull(AnalyticsCache::getCached('system_overview'));
        
        // Other cache entries should remain
        $this->assertNotNull(AnalyticsCache::getCached('student_performance_trends_monthly_12'));
        $this->assertNotNull(AnalyticsCache::getCached('programme_effectiveness'));
    }

    public function test_cache_with_different_parameters()
    {
        AnalyticsCache::clearAll();

        // Get performance trends with different parameters
        $monthly12 = $this->analyticsService->getStudentPerformanceTrends('monthly', 12);
        $weekly6 = $this->analyticsService->getStudentPerformanceTrends('weekly', 6);
        $monthly6 = $this->analyticsService->getStudentPerformanceTrends('monthly', 6);

        // Should create separate cache entries for different parameters
        $cacheKeys = AnalyticsCache::pluck('cache_key')->toArray();
        
        $this->assertContains('student_performance_trends_monthly_12', $cacheKeys);
        $this->assertContains('student_performance_trends_weekly_6', $cacheKeys);
        $this->assertContains('student_performance_trends_monthly_6', $cacheKeys);
        
        // Should have at least 3 different cache entries
        $this->assertGreaterThanOrEqual(3, AnalyticsCache::count());
    }

    public function test_cache_api_endpoints()
    {
        // Test cache refresh endpoint
        $response = $this->actingAs($this->manager)
            ->postJson('/api/analytics/refresh-cache');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Analytics cache refreshed'
            ]);

        // Test clear expired cache endpoint
        $response = $this->actingAs($this->manager)
            ->postJson('/api/analytics/clear-expired-cache');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
    }

    public function test_cache_size_management()
    {
        AnalyticsCache::clearAll();

        // Create many cache entries to test size management
        for ($i = 0; $i < 20; $i++) {
            AnalyticsCache::setCached(
                "test_cache_key_{$i}",
                ['data' => str_repeat('x', 1000)], // 1KB of data
                60
            );
        }

        $cacheCount = AnalyticsCache::count();
        $this->assertEquals(20, $cacheCount);

        // Test that cache entries are being stored properly
        for ($i = 0; $i < 20; $i++) {
            $cached = AnalyticsCache::getCached("test_cache_key_{$i}");
            $this->assertNotNull($cached);
            $this->assertArrayHasKey('data', $cached);
        }
    }

    public function test_cache_error_handling()
    {
        // Test cache operations handle errors gracefully
        
        // Try to get cache with invalid key
        $result = AnalyticsCache::getCached('');
        $this->assertNull($result);

        // Try to set cache with invalid data
        $result = AnalyticsCache::setCached('test_key', null, 60);
        $this->assertInstanceOf(AnalyticsCache::class, $result);

        // Test cache operations with very long keys
        $longKey = str_repeat('a', 300);
        $result = AnalyticsCache::setCached($longKey, ['test' => 'data'], 60);
        $this->assertInstanceOf(AnalyticsCache::class, $result);
    }

    public function test_cache_prevents_stale_data_issues()
    {
        // Create initial data state
        $students = Student::factory()->count(5)->create(['status' => 'active']);
        
        // Get cached overview
        $overview1 = $this->analyticsService->getSystemOverview();
        $this->assertEquals(5, $overview1['students']['active']);

        // Change data
        $students->first()->update(['status' => 'deferred']);

        // Cache should still return old data
        $overview2 = $this->analyticsService->getSystemOverview();
        $this->assertEquals(5, $overview2['students']['active']);

        // Force cache refresh
        AnalyticsCache::clearKey('system_overview');
        $overview3 = $this->analyticsService->getSystemOverview();
        $this->assertEquals(4, $overview3['students']['active']);
    }
}