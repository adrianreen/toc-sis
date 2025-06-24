<?php

namespace App\Console\Commands;

use App\Services\StudentUserLinkingService;
use Illuminate\Console\Command;

class LinkStudentUsers extends Command
{
    protected $signature = 'students:link-users 
                          {--create : Create missing student user accounts}
                          {--link : Link existing users to students by email}
                          {--stats : Show linkage statistics}
                          {--validate : Validate existing linkages}
                          {--all : Run all operations}';

    protected $description = 'Manage student-user account linkages for Azure AD integration';

    public function handle()
    {
        $service = app(StudentUserLinkingService::class);

        if ($this->option('stats') || $this->option('all')) {
            $this->showStatistics($service);
        }

        if ($this->option('validate') || $this->option('all')) {
            $this->validateLinkages($service);
        }

        if ($this->option('link') || $this->option('all')) {
            $this->linkExistingUsers($service);
        }

        if ($this->option('create') || $this->option('all')) {
            $this->createMissingUsers($service);
        }

        // Show final statistics
        if ($this->option('create') || $this->option('link') || $this->option('all')) {
            $this->line('');
            $this->info('Final Statistics:');
            $this->showStatistics($service);
        }
    }

    private function showStatistics(StudentUserLinkingService $service)
    {
        $stats = $service->getLinkageStatistics();

        $this->info('Student-User Linkage Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Students', $stats['total_students']],
                ['Total Student Users', $stats['total_student_users']],
                ['Students with User Accounts', $stats['students_with_users']],
                ['Students without User Accounts', $stats['students_without_users']],
                ['Student Users with Linkage', $stats['student_users_with_linkage']],
                ['Student Users without Linkage', $stats['student_users_without_linkage']],
            ]
        );
    }

    private function validateLinkages(StudentUserLinkingService $service)
    {
        $this->info('Validating student-user linkages...');
        $issues = $service->validateLinkages();

        if (empty($issues)) {
            $this->info('✅ All linkages are valid');
        } else {
            $this->error('❌ Found linkage issues:');
            foreach ($issues as $issue) {
                $this->warn('  - '.$issue);
            }
        }
    }

    private function linkExistingUsers(StudentUserLinkingService $service)
    {
        $this->info('Linking existing users to students by email...');
        $results = $service->linkExistingUsersByEmail();

        $this->info("✅ Linked {$results['linked']} users to students");
        if ($results['skipped'] > 0) {
            $this->warn("⏭️  Skipped {$results['skipped']} users (no matching student)");
        }

        if (! empty($results['errors'])) {
            $this->error('❌ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->error('  - '.$error);
            }
        }
    }

    private function createMissingUsers(StudentUserLinkingService $service)
    {
        $this->info('Creating user accounts for students without them...');

        if (! $this->confirm('This will create user accounts for all students without them. Continue?')) {
            $this->warn('Operation cancelled');

            return;
        }

        $results = $service->createMissingStudentUsers();

        $this->info("✅ Created {$results['created']} new student user accounts");
        $this->info("✅ Linked {$results['linked']} existing users to students");

        if (! empty($results['errors'])) {
            $this->error('❌ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->error('  - '.$error);
            }
        }
    }
}
