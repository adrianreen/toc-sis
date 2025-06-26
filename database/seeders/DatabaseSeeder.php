<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * COMPREHENSIVE SEEDER FOR COMPLETE DATA RESTORATION
     * This seeder rebuilds the entire database with realistic data.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting comprehensive database seeding...');

        // Core system data (must come first)
        $this->command->info('Creating core system data...');
        $this->call([
            UserSeeder::class,              // Staff and test users
            PolicyCategorySeeder::class,    // Policy categories
            EmailTemplateSeeder::class,     // Email templates
        ]);

        // Academic structure (programmes and modules)
        $this->command->info('Creating academic structure...');
        $this->call([
            ProgrammeSeeder::class,         // Academic programmes
            ModuleSeeder::class,            // Modules with assessment strategies
            ProgrammeInstanceSeeder::class, // Programme instances with intakes
            ModuleInstanceSeeder::class,    // Module instances with tutors
        ]);

        // Student data and enrolments
        $this->command->info('Creating student data...');
        $this->call([
            StudentSeeder::class,           // 200+ students
            EnrolmentSeeder::class,         // Student enrolments
            StudentGradeRecordSeeder::class, // Assessment grades
        ]);

        // Policies and content
        $this->command->info('Creating policies and content...');
        $this->call([
            PolicySeeder::class,            // College policies
        ]);

        $this->command->info('✅ COMPREHENSIVE SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->line('');
        $this->command->info('📊 Database now contains:');
        $this->command->info('  • 10 academic programmes with multiple delivery modes');
        $this->command->info('  • 15+ modules with realistic assessment strategies');
        $this->command->info('  • Multiple programme instances for current and past years');
        $this->command->info('  • 200+ realistic student records');
        $this->command->info('  • 25+ staff members across all departments');
        $this->command->info('  • Thousands of grade records with realistic distributions');
        $this->command->info('  • Comprehensive student enrolments (programme and standalone)');
        $this->command->info('  • Complete policy framework with detailed content');
        $this->command->info('  • Email templates and notification system ready');
        $this->command->line('');
        $this->command->info('🎯 The system is now fully populated and ready for use!');
        $this->command->info('💡 Test login credentials: manager@test.local / password123');
    }
}
