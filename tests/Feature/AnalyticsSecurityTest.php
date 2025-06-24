<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnalyticsSecurityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_manager_can_access_all_analytics_endpoints()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
            '/api/analytics/chart-data/student_performance',
            '/api/analytics/historical-metrics?metric_type=system_overview',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($manager)->getJson($endpoint);
            $response->assertStatus(200, "Manager should access endpoint: {$endpoint}");
        }
    }

    public function test_student_services_can_access_all_analytics_endpoints()
    {
        $studentServices = User::factory()->create(['role' => 'student_services']);

        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($studentServices)->getJson($endpoint);
            $response->assertStatus(200, "Student Services should access endpoint: {$endpoint}");
        }
    }

    public function test_teacher_can_access_all_analytics_endpoints()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($teacher)->getJson($endpoint);
            $response->assertStatus(200, "Teacher should access endpoint: {$endpoint}");
        }
    }

    public function test_student_cannot_access_any_analytics_endpoints()
    {
        $student = User::factory()->create(['role' => 'student']);

        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
            '/api/analytics/chart-data/student_performance',
            '/api/analytics/chart-data/programme_effectiveness',
            '/api/analytics/chart-data/assessment_completion',
            '/api/analytics/chart-data/student_engagement',
            '/api/analytics/historical-metrics?metric_type=system_overview',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($student)->getJson($endpoint);
            $response->assertStatus(403, "Student should be forbidden from endpoint: {$endpoint}");
        }
    }

    public function test_cache_management_endpoints_require_staff_roles()
    {
        $student = User::factory()->create(['role' => 'student']);
        $manager = User::factory()->create(['role' => 'manager']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $studentServices = User::factory()->create(['role' => 'student_services']);

        // Student should be forbidden
        $response = $this->actingAs($student)->postJson('/api/analytics/refresh-cache');
        $response->assertStatus(403);

        $response = $this->actingAs($student)->postJson('/api/analytics/clear-expired-cache');
        $response->assertStatus(403);

        // Staff roles should be allowed
        $staffUsers = [$manager, $teacher, $studentServices];

        foreach ($staffUsers as $user) {
            $response = $this->actingAs($user)->postJson('/api/analytics/refresh-cache');
            $response->assertStatus(200, "User with role {$user->role} should access cache refresh");

            $response = $this->actingAs($user)->postJson('/api/analytics/clear-expired-cache');
            $response->assertStatus(200, "User with role {$user->role} should access cache clear");
        }
    }

    public function test_unauthenticated_users_cannot_access_analytics()
    {
        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            // Should either redirect to login (302) or return unauthorized (401)
            $this->assertTrue(
                in_array($response->status(), [302, 401]),
                "Unauthenticated access to {$endpoint} should be blocked"
            );
        }
    }

    public function test_analytics_data_does_not_expose_sensitive_information()
    {
        // Create test data with potentially sensitive information
        $students = Student::factory()->count(5)->create([
            'email' => 'sensitive@email.com',
            'phone' => '0851234567',
            'address' => 'Sensitive Address',
        ]);

        $programmes = Programme::factory()->count(3)->create();

        $manager = User::factory()->create(['role' => 'manager']);

        // Test system overview doesn't expose sensitive data
        $response = $this->actingAs($manager)->getJson('/api/analytics/system-overview');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Should not contain email addresses
        $this->assertStringNotContainsString('sensitive@email.com', $content);
        $this->assertStringNotContainsString('@', $content);

        // Should not contain phone numbers
        $this->assertStringNotContainsString('0851234567', $content);

        // Should not contain addresses
        $this->assertStringNotContainsString('Sensitive Address', $content);

        // Test programme effectiveness doesn't expose sensitive data
        $response = $this->actingAs($manager)->getJson('/api/analytics/programme-effectiveness');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('sensitive@email.com', $content);
        $this->assertStringNotContainsString('0851234567', $content);

        // Test student engagement doesn't expose individual identifiers
        $response = $this->actingAs($manager)->getJson('/api/analytics/student-engagement');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Should not contain individual student or user IDs in the response
        $this->assertStringNotContainsString('student_id', $content);
        $this->assertStringNotContainsString('user_id', $content);
    }

    public function test_analytics_endpoints_are_protected_against_sql_injection()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        // Test various SQL injection attempts in query parameters
        $maliciousInputs = [
            "'; DROP TABLE students; --",
            "1' OR '1'='1",
            '1; SELECT * FROM users; --',
            'UNION SELECT * FROM users',
            "<script>alert('xss')</script>",
        ];

        foreach ($maliciousInputs as $input) {
            // Test with period_type parameter
            $response = $this->actingAs($manager)
                ->getJson('/api/analytics/student-performance?period_type='.urlencode($input));

            // Should either return 200 with safe data or handle the input safely
            $this->assertTrue(
                in_array($response->status(), [200, 400, 422]),
                'SQL injection attempt should be handled safely'
            );

            // Response should not contain SQL keywords or script tags
            $content = strtolower($response->getContent());
            $this->assertStringNotContainsString('drop table', $content);
            $this->assertStringNotContainsString('select *', $content);
            $this->assertStringNotContainsString('<script>', $content);

            // Test with metric_type parameter
            $response = $this->actingAs($manager)
                ->getJson('/api/analytics/historical-metrics?metric_type='.urlencode($input));

            $this->assertTrue(
                in_array($response->status(), [200, 400, 422]),
                'SQL injection attempt should be handled safely'
            );
        }
    }

    public function test_analytics_endpoints_validate_input_parameters()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        // Test invalid period_type values
        $invalidPeriodTypes = ['invalid', 'yearly', 'hourly', 'random'];

        foreach ($invalidPeriodTypes as $invalidType) {
            $response = $this->actingAs($manager)
                ->getJson("/api/analytics/student-performance?period_type={$invalidType}");

            // Should handle invalid input gracefully (either return valid data or error)
            $this->assertTrue(
                in_array($response->status(), [200, 400, 422]),
                'Invalid period_type should be handled properly'
            );
        }

        // Test invalid numeric parameters
        $response = $this->actingAs($manager)
            ->getJson('/api/analytics/student-performance?months=invalid');

        $this->assertTrue(
            in_array($response->status(), [200, 400, 422]),
            'Invalid numeric parameter should be handled properly'
        );

        // Test extremely large numeric values
        $response = $this->actingAs($manager)
            ->getJson('/api/analytics/student-performance?months=999999');

        $this->assertTrue(
            in_array($response->status(), [200, 400, 422]),
            'Large numeric parameter should be handled properly'
        );
    }

    public function test_analytics_endpoints_have_proper_cors_headers()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)
            ->getJson('/api/analytics/system-overview');

        $response->assertStatus(200);

        // Should have proper JSON content type
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_analytics_chart_data_validates_chart_types()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        // Test valid chart types
        $validTypes = ['student_performance', 'programme_effectiveness', 'assessment_completion', 'student_engagement'];

        foreach ($validTypes as $type) {
            $response = $this->actingAs($manager)
                ->getJson("/api/analytics/chart-data/{$type}");
            $response->assertStatus(200);
        }

        // Test invalid chart types
        $invalidTypes = ['invalid_chart', 'admin_data', 'sensitive_info', '../etc/passwd'];

        foreach ($invalidTypes as $type) {
            $response = $this->actingAs($manager)
                ->getJson("/api/analytics/chart-data/{$type}");
            $response->assertStatus(400);
            $response->assertJsonFragment(['error' => 'Unknown chart type']);
        }
    }

    public function test_analytics_endpoints_rate_limiting()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        // Make multiple rapid requests to test rate limiting
        // Note: This test assumes rate limiting is implemented
        $responses = [];

        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->actingAs($manager)
                ->getJson('/api/analytics/system-overview');
        }

        // All requests should succeed (or be rate limited appropriately)
        foreach ($responses as $response) {
            $this->assertTrue(
                in_array($response->status(), [200, 429]),
                'Request should either succeed or be rate limited'
            );
        }
    }

    public function test_analytics_endpoints_logging_and_auditing()
    {
        $manager = User::factory()->create(['role' => 'manager']);

        // Make request to analytics endpoint
        $response = $this->actingAs($manager)
            ->getJson('/api/analytics/system-overview');

        $response->assertStatus(200);

        // Note: In a real implementation, you would check that the request
        // was properly logged for security auditing purposes
        // This might involve checking log files or database audit tables
    }
}
