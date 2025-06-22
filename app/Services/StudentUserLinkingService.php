<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentUserLinkingService
{
    /**
     * Create user accounts for students who don't have them
     * This is primarily for development/testing purposes
     */
    public function createMissingStudentUsers(): array
    {
        $results = [
            'created' => 0,
            'linked' => 0,
            'errors' => []
        ];

        DB::transaction(function () use (&$results) {
            // Get students without linked user accounts
            $studentsWithoutUsers = Student::whereDoesntHave('user')->get();
            
            foreach ($studentsWithoutUsers as $student) {
                try {
                    // Check if a user with this email already exists
                    $existingUser = User::where('email', $student->email)->first();
                    
                    if ($existingUser) {
                        // Link existing user to student
                        if ($existingUser->role === 'student') {
                            $existingUser->student_id = $student->id;
                            $existingUser->save();
                            $results['linked']++;
                        }
                    } else {
                        // Create new student user
                        $user = User::create([
                            'name' => $student->full_name,
                            'email' => $student->email,
                            'password' => Hash::make(Str::random(32)), // Random password
                            'role' => 'student',
                            'student_id' => $student->id,
                            'azure_id' => null, // Will be set when they first login via Azure
                            'azure_groups' => ['Students'] // Default student group
                        ]);
                        $results['created']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Failed to process student {$student->email}: " . $e->getMessage();
                }
            }
        });

        return $results;
    }

    /**
     * Link existing users to students based on email matching
     */
    public function linkExistingUsersByEmail(): array
    {
        $results = [
            'linked' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        $studentUsers = User::where('role', 'student')->whereNull('student_id')->get();
        
        foreach ($studentUsers as $user) {
            try {
                $student = Student::where('email', $user->email)->first();
                
                if ($student) {
                    $user->student_id = $student->id;
                    $user->save();
                    $results['linked']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Failed to link user {$user->email}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get statistics about student-user linkages
     */
    public function getLinkageStatistics(): array
    {
        return [
            'total_students' => Student::count(),
            'total_student_users' => User::where('role', 'student')->count(),
            'students_with_users' => Student::whereHas('user')->count(),
            'students_without_users' => Student::whereDoesntHave('user')->count(),
            'student_users_with_linkage' => User::where('role', 'student')->whereNotNull('student_id')->count(),
            'student_users_without_linkage' => User::where('role', 'student')->whereNull('student_id')->count(),
        ];
    }

    /**
     * Validate all student-user relationships
     */
    public function validateLinkages(): array
    {
        $issues = [];
        
        // Check for users with invalid student_id
        $invalidLinks = User::whereNotNull('student_id')
            ->whereDoesntHave('student')
            ->get();
            
        foreach ($invalidLinks as $user) {
            $issues[] = "User {$user->email} has invalid student_id: {$user->student_id}";
        }
        
        // Check for duplicate student linkages
        $duplicateLinks = User::select('student_id')
            ->whereNotNull('student_id')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        foreach ($duplicateLinks as $duplicate) {
            $users = User::where('student_id', $duplicate->student_id)->get();
            $emails = $users->pluck('email')->implode(', ');
            $issues[] = "Multiple users linked to student ID {$duplicate->student_id}: {$emails}";
        }
        
        return $issues;
    }
}