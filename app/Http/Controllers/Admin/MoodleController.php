<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleInstance;
use App\Models\Student;
use App\Services\MoodleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoodleController extends Controller
{
    protected MoodleService $moodleService;

    public function __construct(MoodleService $moodleService)
    {
        $this->middleware(['auth', 'role:manager,student_services']);
        $this->moodleService = $moodleService;
    }

    /**
     * Show Moodle integration dashboard
     */
    public function index()
    {
        $connectionTest = $this->moodleService->testConnection();

        $stats = [
            'courses_with_moodle' => ModuleInstance::whereNotNull('moodle_course_id')->count(),
            'total_courses' => ModuleInstance::count(),
            'students_with_moodle' => Student::whereNotNull('moodle_user_id')->count(),
            'total_students' => Student::count(),
        ];

        return view('admin.moodle.index', compact('connectionTest', 'stats'));
    }

    /**
     * Create course in Moodle
     */
    public function createCourse(Request $request, ModuleInstance $moduleInstance)
    {
        try {
            if ($moduleInstance->moodle_course_id) {
                return back()->with('error', 'Course already exists in Moodle.');
            }

            $result = $this->moodleService->createCourse($moduleInstance);

            return back()->with('success', "Course '{$result['fullname']}' created successfully in Moodle with ID: {$result['id']}");
        } catch (\Exception $e) {
            Log::error('Failed to create course in Moodle', [
                'module_instance_id' => $moduleInstance->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to create course in Moodle: '.$e->getMessage());
        }
    }

    /**
     * Enroll single student in course
     */
    public function enrollStudent(Request $request, ModuleInstance $moduleInstance, Student $student)
    {
        try {
            $this->moodleService->enrollStudent($student, $moduleInstance);

            return back()->with('success', "Student {$student->first_name} {$student->last_name} enrolled successfully in Moodle course.");
        } catch (\Exception $e) {
            Log::error('Failed to enroll student in Moodle', [
                'student_id' => $student->id,
                'module_instance_id' => $moduleInstance->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to enroll student in Moodle: '.$e->getMessage());
        }
    }

    /**
     * Bulk enroll all students in a cohort to course
     */
    public function bulkEnrollCohort(Request $request, ModuleInstance $moduleInstance)
    {
        try {
            // Get all students enrolled in this module instance
            $students = Student::whereHas('studentModuleEnrolments', function ($query) use ($moduleInstance) {
                $query->where('module_instance_id', $moduleInstance->id);
            })->get();

            if ($students->isEmpty()) {
                return back()->with('error', 'No students found enrolled in this module instance.');
            }

            $result = $this->moodleService->bulkEnrollStudents($students->toArray(), $moduleInstance);

            return back()->with('success', "Successfully enrolled {$result['enrolled_count']} students in Moodle course.");
        } catch (\Exception $e) {
            Log::error('Failed to bulk enroll students in Moodle', [
                'module_instance_id' => $moduleInstance->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to bulk enroll students in Moodle: '.$e->getMessage());
        }
    }

    /**
     * Show course details from Moodle
     */
    public function showCourse(ModuleInstance $moduleInstance)
    {
        try {
            if (! $moduleInstance->moodle_course_id) {
                return back()->with('error', 'Course not yet created in Moodle.');
            }

            $courseData = $this->moodleService->getCourse($moduleInstance->moodle_course_id);
            $enrollments = $this->moodleService->getCourseEnrollments($moduleInstance->moodle_course_id);

            return view('admin.moodle.course-details', compact('moduleInstance', 'courseData', 'enrollments'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to fetch course details from Moodle: '.$e->getMessage());
        }
    }

    /**
     * Test connection to Moodle
     */
    public function testConnection()
    {
        $result = $this->moodleService->testConnection();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: '.$result['error'],
            ], 400);
        }
    }

    /**
     * Sync all courses to Moodle
     */
    public function syncAllCourses(Request $request)
    {
        try {
            $moduleInstances = ModuleInstance::whereNull('moodle_course_id')
                ->with(['module', 'cohort'])
                ->get();

            $created = 0;
            $errors = [];

            foreach ($moduleInstances as $moduleInstance) {
                try {
                    $this->moodleService->createCourse($moduleInstance);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create {$moduleInstance->instance_code}: ".$e->getMessage();
                }
            }

            $message = "Created {$created} courses in Moodle.";
            if (! empty($errors)) {
                $message .= ' Errors: '.implode('; ', $errors);
            }

            return back()->with($created > 0 ? 'success' : 'error', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: '.$e->getMessage());
        }
    }
}
