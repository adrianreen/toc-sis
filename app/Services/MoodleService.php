<?php

namespace App\Services;

use App\Models\ModuleInstance;
use App\Models\Student;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class MoodleService
{
    protected Client $client;

    protected string $baseUrl;

    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('moodle.url');
        $this->token = config('moodle.token');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => config('moodle.verify_ssl', true),
        ]);
    }

    /**
     * Make a request to the Moodle Web Services API
     */
    public function makeRequest(string $function, array $parameters = []): array
    {
        try {
            $response = $this->client->post('/webservice/rest/server.php', [
                'form_params' => [
                    'wstoken' => $this->token,
                    'wsfunction' => $function,
                    'moodlewsrestformat' => 'json',
                ] + $parameters,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['exception'])) {
                throw new \Exception('Moodle API Error: '.$data['message']);
            }

            return $data;
        } catch (RequestException $e) {
            Log::error('Moodle API request failed', [
                'function' => $function,
                'parameters' => $parameters,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to communicate with Moodle: '.$e->getMessage());
        }
    }

    /**
     * Create a new course in Moodle
     */
    public function createCourse(ModuleInstance $moduleInstance): array
    {
        $courseData = [
            'courses[0][fullname]' => $moduleInstance->module->title.' - '.$moduleInstance->start_date->format('M Y'),
            'courses[0][shortname]' => $moduleInstance->module->module_code.'_'.$moduleInstance->id,
            'courses[0][categoryid]' => config('moodle.default_category_id', 1),
            'courses[0][summary]' => 'Module: '.$moduleInstance->module->title.' (Credits: '.$moduleInstance->module->credit_value.')',
            'courses[0][summaryformat]' => 1, // HTML format
            'courses[0][format]' => 'topics',
            'courses[0][showgrades]' => 1,
            'courses[0][newsitems]' => 5,
            'courses[0][startdate]' => strtotime($moduleInstance->start_date),
            'courses[0][enddate]' => strtotime($moduleInstance->end_date),
            'courses[0][visible]' => 1,
        ];

        $result = $this->makeRequest('core_course_create_courses', $courseData);

        if (isset($result[0]['id'])) {
            Log::info('Moodle course created successfully', [
                'module_instance_id' => $moduleInstance->id,
                'moodle_course_id' => $result[0]['id'],
                'course_shortname' => $moduleInstance->instance_code,
            ]);

            // Store the Moodle course ID in the module instance
            $moduleInstance->update(['moodle_course_id' => $result[0]['id']]);

            return $result[0];
        }

        throw new \Exception('Failed to create course in Moodle');
    }

    /**
     * Create or update a user in Moodle
     */
    public function createOrUpdateUser(Student $student): array
    {
        // First, try to get the user by email
        $existingUser = $this->getUserByEmail($student->email);

        if ($existingUser) {
            // Update existing user
            $userData = [
                'users[0][id]' => $existingUser['id'],
                'users[0][firstname]' => $student->first_name,
                'users[0][lastname]' => $student->last_name,
                'users[0][email]' => $student->email,
            ];

            $result = $this->makeRequest('core_user_update_users', $userData);

            return $existingUser;
        } else {
            // Create new user
            $userData = [
                'users[0][username]' => $this->generateUsername($student),
                'users[0][password]' => $this->generateTemporaryPassword(),
                'users[0][firstname]' => $student->first_name,
                'users[0][lastname]' => $student->last_name,
                'users[0][email]' => $student->email,
                'users[0][auth]' => 'manual',
                'users[0][confirmed]' => 1,
                'users[0][lang]' => 'en',
                'users[0][timezone]' => config('app.timezone', 'Europe/Dublin'),
            ];

            $result = $this->makeRequest('core_user_create_users', $userData);

            if (isset($result[0]['id'])) {
                Log::info('Moodle user created successfully', [
                    'student_id' => $student->id,
                    'moodle_user_id' => $result[0]['id'],
                    'username' => $userData['users[0][username]'],
                ]);

                // Store the Moodle user ID
                $student->update(['moodle_user_id' => $result[0]['id']]);

                return $result[0];
            }

            throw new \Exception('Failed to create user in Moodle');
        }
    }

    /**
     * Enroll a student in a course
     */
    public function enrollStudent(Student $student, ModuleInstance $moduleInstance, string $role = 'student'): bool
    {
        // Ensure user exists in Moodle
        if (! $student->moodle_user_id) {
            $this->createOrUpdateUser($student);
        }

        // Ensure course exists in Moodle
        if (! $moduleInstance->moodle_course_id) {
            $this->createCourse($moduleInstance);
        }

        $roleId = $this->getRoleId($role);

        $enrollmentData = [
            'enrolments[0][roleid]' => $roleId,
            'enrolments[0][userid]' => $student->moodle_user_id,
            'enrolments[0][courseid]' => $moduleInstance->moodle_course_id,
            'enrolments[0][timestart]' => strtotime($moduleInstance->start_date),
            'enrolments[0][timeend]' => strtotime($moduleInstance->end_date),
        ];

        try {
            $result = $this->makeRequest('enrol_manual_enrol_users', $enrollmentData);

            Log::info('Student enrolled in Moodle course', [
                'student_id' => $student->id,
                'module_instance_id' => $moduleInstance->id,
                'moodle_user_id' => $student->moodle_user_id,
                'moodle_course_id' => $moduleInstance->moodle_course_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to enroll student in Moodle course', [
                'student_id' => $student->id,
                'module_instance_id' => $moduleInstance->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk enroll students in a course
     */
    public function bulkEnrollStudents(array $students, ModuleInstance $moduleInstance, string $role = 'student'): array
    {
        $results = [];
        $roleId = $this->getRoleId($role);

        // Ensure course exists
        if (! $moduleInstance->moodle_course_id) {
            $this->createCourse($moduleInstance);
        }

        // Prepare enrollment data for all students
        $enrollmentData = [];
        foreach ($students as $index => $student) {
            // Ensure user exists in Moodle
            if (! $student->moodle_user_id) {
                $this->createOrUpdateUser($student);
            }

            $enrollmentData["enrolments[{$index}][roleid]"] = $roleId;
            $enrollmentData["enrolments[{$index}][userid]"] = $student->moodle_user_id;
            $enrollmentData["enrolments[{$index}][courseid]"] = $moduleInstance->moodle_course_id;
            $enrollmentData["enrolments[{$index}][timestart]"] = strtotime($moduleInstance->start_date);
            $enrollmentData["enrolments[{$index}][timeend]"] = strtotime($moduleInstance->end_date);
        }

        try {
            $result = $this->makeRequest('enrol_manual_enrol_users', $enrollmentData);

            Log::info('Bulk enrollment completed', [
                'module_instance_id' => $moduleInstance->id,
                'student_count' => count($students),
                'moodle_course_id' => $moduleInstance->moodle_course_id,
            ]);

            return ['success' => true, 'enrolled_count' => count($students)];
        } catch (\Exception $e) {
            Log::error('Bulk enrollment failed', [
                'module_instance_id' => $moduleInstance->id,
                'student_count' => count($students),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get course information from Moodle
     */
    public function getCourse(int $courseId): array
    {
        $result = $this->makeRequest('core_course_get_courses', [
            'options[ids][0]' => $courseId,
        ]);

        if (isset($result[0])) {
            return $result[0];
        }

        throw new \Exception("Course not found in Moodle: {$courseId}");
    }

    /**
     * Get enrolled users in a course
     */
    public function getCourseEnrollments(int $courseId): array
    {
        return $this->makeRequest('core_enrol_get_enrolled_users', [
            'courseid' => $courseId,
        ]);
    }

    /**
     * Get user by email
     */
    protected function getUserByEmail(string $email): ?array
    {
        try {
            $result = $this->makeRequest('core_user_get_users', [
                'criteria[0][key]' => 'email',
                'criteria[0][value]' => $email,
            ]);

            return isset($result['users'][0]) ? $result['users'][0] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate username for Moodle
     */
    protected function generateUsername(Student $student): string
    {
        $baseUsername = strtolower($student->first_name.'.'.$student->last_name);
        $baseUsername = preg_replace('/[^a-z0-9.]/', '', $baseUsername);

        // Add student number to make it unique
        return $baseUsername.'.'.$student->student_number;
    }

    /**
     * Generate temporary password
     */
    protected function generateTemporaryPassword(): string
    {
        return 'temp'.rand(1000, 9999).'!';
    }

    /**
     * Get role ID by role name
     */
    protected function getRoleId(string $roleName): int
    {
        $roleMap = [
            'student' => 5,
            'teacher' => 3,
            'editingteacher' => 3,
            'manager' => 1,
        ];

        return $roleMap[$roleName] ?? 5; // Default to student
    }

    /**
     * Test connection to Moodle
     */
    public function testConnection(): array
    {
        try {
            $result = $this->makeRequest('core_webservice_get_site_info');

            return [
                'success' => true,
                'site_name' => $result['sitename'] ?? 'Unknown',
                'moodle_version' => $result['release'] ?? 'Unknown',
                'user_count' => $result['usercount'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Set up a Moodle course for a repeat assessment
     */
    public function setupRepeatAssessmentCourse($repeatAssessment): ?string
    {
        try {
            $moduleInstance = $repeatAssessment->module_instance;
            $student = $repeatAssessment->student;
            // Get assessment component name from the repeat assessment record

            // Check if a course already exists for this module instance
            if ($moduleInstance->moodle_course_id) {
                // Course exists, just enroll the student
                $enrolled = $this->enrollStudent($student, $moduleInstance);

                if ($enrolled) {
                    Log::info('Student enrolled in existing Moodle course for repeat assessment', [
                        'student_id' => $student->id,
                        'course_id' => $moduleInstance->moodle_course_id,
                        'repeat_assessment_id' => $repeatAssessment->id,
                    ]);

                    return $moduleInstance->moodle_course_id;
                } else {
                    throw new \Exception('Failed to enroll student in existing Moodle course');
                }
            }

            // Create a new course for this repeat assessment
            $courseName = "Repeat Assessment - {$moduleInstance->module->title} - {$repeatAssessment->assessment_component_name}";
            $courseShortName = "REPEAT_{$moduleInstance->module->module_code}_{$repeatAssessment->id}";

            $courseData = [
                'fullname' => $courseName,
                'shortname' => $courseShortName,
                'summary' => "Repeat assessment course for {$repeatAssessment->assessment_component_name} in {$moduleInstance->module->title}",
                'categoryid' => config('moodle.default_category_id', 1),
                'visible' => 1,
                'startdate' => now()->timestamp,
                'enddate' => $repeatAssessment->repeat_due_date->timestamp,
            ];

            $response = $this->makeRequest('core_course_create_courses', [
                'courses' => [$courseData],
            ]);

            if (empty($response) || ! isset($response[0]['id'])) {
                throw new \Exception('Failed to create Moodle course');
            }

            $courseId = $response[0]['id'];

            // Enroll the student in the new course
            $enrolled = $this->enrollStudentInCourse($student, $courseId);

            if (! $enrolled) {
                throw new \Exception('Failed to enroll student in new Moodle course');
            }

            // Update the module instance with the course ID for future use
            $moduleInstance->update(['moodle_course_id' => $courseId]);

            Log::info('New Moodle course created for repeat assessment', [
                'student_id' => $student->id,
                'course_id' => $courseId,
                'course_name' => $courseName,
                'repeat_assessment_id' => $repeatAssessment->id,
            ]);

            return (string) $courseId;

        } catch (\Exception $e) {
            Log::error('Failed to setup Moodle course for repeat assessment', [
                'repeat_assessment_id' => $repeatAssessment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Enroll a student in a specific Moodle course by course ID
     */
    public function enrollStudentInCourse(Student $student, int $courseId, string $role = 'student'): bool
    {
        try {
            // First, ensure the user exists in Moodle
            $moodleUserId = $this->createOrUpdateUser($student);

            if (! $moodleUserId) {
                return false;
            }

            // Get the role ID for the specified role
            $roleId = $this->getRoleId($role);

            if (! $roleId) {
                Log::error('Invalid role specified for Moodle enrollment', ['role' => $role]);

                return false;
            }

            // Enroll the user in the course
            $response = $this->makeRequest('enrol_manual_enrol_users', [
                'enrolments' => [[
                    'roleid' => $roleId,
                    'userid' => $moodleUserId,
                    'courseid' => $courseId,
                ]],
            ]);

            Log::info('Student enrolled in Moodle course', [
                'student_id' => $student->id,
                'moodle_user_id' => $moodleUserId,
                'course_id' => $courseId,
                'role' => $role,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to enroll student in Moodle course', [
                'student_id' => $student->id,
                'course_id' => $courseId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
