<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Programme;
use App\Models\Enrolment;
use App\Models\StudentAssessment;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a manager user for authentication
        $this->manager = User::factory()->create(['role' => 'manager']);
    }

    public function test_system_overview_endpoint_returns_valid_structure()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/system-overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'students' => [
                    'total',
                    'active',
                    'enrolled',
                    'deferred',
                    'completed'
                ],
                'programmes' => [
                    'total',
                    'active'
                ],
                'assessments' => [
                    'total',
                    'pending',
                    'submitted',
                    'graded',
                    'passed',
                    'failed'
                ],
                'enrollments' => [
                    'total',
                    'active',
                    'completed',
                    'deferred'
                ],
                'generated_at'
            ]);
    }

    public function test_system_overview_with_real_data()
    {
        // Create test data
        Student::factory()->count(5)->create(['status' => 'active']);
        Student::factory()->count(3)->create(['status' => 'enrolled']);
        Student::factory()->count(2)->create(['status' => 'completed']);
        
        Programme::factory()->count(4)->create(['is_active' => true]);
        Programme::factory()->count(1)->create(['is_active' => false]);

        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/system-overview');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        $this->assertEquals(10, $data['students']['total']);
        $this->assertEquals(5, $data['students']['active']);
        $this->assertEquals(3, $data['students']['enrolled']);
        $this->assertEquals(2, $data['students']['completed']);
        $this->assertEquals(5, $data['programmes']['total']);
        $this->assertEquals(4, $data['programmes']['active']);
    }

    public function test_student_performance_endpoint_with_parameters()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/student-performance?period_type=weekly&months=6');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'assessment_trends',
                'enrollment_trends',
                'period_type',
                'generated_at'
            ]);
            
        $data = $response->json();
        $this->assertEquals('weekly', $data['period_type']);
    }

    public function test_programme_effectiveness_endpoint()
    {
        // Create programme with enrollments
        $programme = Programme::factory()->create(['is_active' => true]);
        $students = Student::factory()->count(3)->create();
        
        foreach ($students as $student) {
            Enrolment::factory()->create([
                'student_id' => $student->id,
                'programme_id' => $programme->id,
                'status' => 'active'
            ]);
        }

        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/programme-effectiveness');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'programmes' => [
                    '*' => [
                        'programme_id',
                        'programme_code',
                        'programme_title',
                        'total_enrollments',
                        'active_enrollments',
                        'completed_enrollments',
                        'completion_rate',
                        'average_grade',
                        'pass_rate'
                    ]
                ],
                'generated_at'
            ]);
            
        $data = $response->json();
        $this->assertCount(1, $data['programmes']);
        $this->assertEquals(3, $data['programmes'][0]['total_enrollments']);
    }

    public function test_assessment_completion_endpoint_with_different_periods()
    {
        // Test weekly period
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/assessment-completion?period_type=weekly&periods=8');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'completion_rates',
                'period_type',
                'generated_at'
            ]);

        // Test monthly period
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/assessment-completion?period_type=monthly&periods=12');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('monthly', $data['period_type']);
    }

    public function test_student_engagement_endpoint()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/student-engagement');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_active_students',
                'recently_active_students',
                'engagement_rate',
                'submission_patterns',
                'generated_at'
            ]);
            
        $data = $response->json();
        $this->assertIsArray($data['submission_patterns']);
        $this->assertIsNumeric($data['engagement_rate']);
    }

    public function test_chart_data_endpoints()
    {
        $chartTypes = [
            'student_performance',
            'programme_effectiveness',
            'assessment_completion',
            'student_engagement'
        ];

        foreach ($chartTypes as $type) {
            $response = $this->actingAs($this->manager)
                ->getJson("/api/analytics/chart-data/{$type}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'type',
                    'data' => [
                        'labels',
                        'datasets'
                    ],
                    'options'
                ]);
                
            $data = $response->json();
            $this->assertIsString($data['type']);
            $this->assertIsArray($data['data']['datasets']);
        }
    }

    public function test_chart_data_invalid_type()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/chart-data/invalid_type');

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'Unknown chart type'
            ]);
    }

    public function test_historical_metrics_endpoint()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/historical-metrics?metric_type=system_overview&period_type=daily&limit=30');

        $response->assertStatus(200);
    }

    public function test_historical_metrics_missing_required_parameter()
    {
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/historical-metrics');

        $response->assertStatus(400)
            ->assertJsonFragment([
                'error' => 'metric_type is required'
            ]);
    }

    public function test_refresh_cache_endpoint()
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/analytics/refresh-cache');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Analytics cache refreshed'
            ]);
    }

    public function test_clear_expired_cache_endpoint()
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/analytics/clear-expired-cache');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);
            
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertStringContains('Cleared', $data['message']);
    }

    public function test_analytics_endpoints_handle_exceptions_gracefully()
    {
        // Mock AnalyticsService to throw exception
        $this->mock(AnalyticsService::class, function ($mock) {
            $mock->shouldReceive('getSystemOverview')
                ->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/system-overview');

        $response->assertStatus(500)
            ->assertJsonFragment([
                'error' => 'Failed to get system overview'
            ]);
    }

    public function test_analytics_endpoints_require_authentication()
    {
        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
            '/api/analytics/chart-data/student_performance',
            '/api/analytics/historical-metrics?metric_type=system_overview'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            // Should redirect to login or return 401
            $this->assertTrue(
                $response->status() === 302 || $response->status() === 401,
                "Endpoint {$endpoint} should require authentication"
            );
        }
    }

    public function test_cache_management_endpoints_require_authentication()
    {
        $response = $this->postJson('/api/analytics/refresh-cache');
        $this->assertTrue($response->status() === 302 || $response->status() === 401);

        $response = $this->postJson('/api/analytics/clear-expired-cache');
        $this->assertTrue($response->status() === 302 || $response->status() === 401);
    }

    public function test_analytics_data_consistency_across_calls()
    {
        // Create stable test data
        Student::factory()->count(10)->create(['status' => 'active']);
        Programme::factory()->count(5)->create(['is_active' => true]);

        // Make multiple calls and ensure consistency
        $response1 = $this->actingAs($this->manager)
            ->getJson('/api/analytics/system-overview');
        
        $response2 = $this->actingAs($this->manager)
            ->getJson('/api/analytics/system-overview');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Data should be identical (cached)
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_analytics_performance_within_acceptable_limits()
    {
        // Create substantial test data
        Student::factory()->count(100)->create();
        Programme::factory()->count(20)->create();

        $startTime = microtime(true);
        
        $response = $this->actingAs($this->manager)
            ->getJson('/api/analytics/system-overview');
            
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        
        // Should complete within 2 seconds (2000ms)
        $this->assertLessThan(2000, $executionTime, 'Analytics endpoint took too long to respond');
    }
}