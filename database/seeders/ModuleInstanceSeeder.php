<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\ProgrammeInstance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModuleInstanceSeeder extends Seeder
{
    public function run(): void
    {
        $modules = Module::all();
        $teachers = User::where('role', 'teacher')->get();
        $programmeInstances = ProgrammeInstance::all();

        foreach ($modules as $module) {
            $this->createModuleInstances($module, $teachers, $programmeInstances);
        }
    }

    private function createModuleInstances(Module $module, $teachers, $programmeInstances)
    {
        $currentYear = Carbon::now()->year;
        $currentDate = Carbon::now();

        // Create instances based on async cadence for standalone modules
        if ($module->allows_standalone_enrolment) {
            switch ($module->async_instance_cadence) {
                case 'monthly':
                    $this->createMonthlyInstances($module, $teachers, $currentYear, $currentDate);
                    break;
                case 'quarterly':
                    $this->createQuarterlyInstances($module, $teachers, $currentYear, $currentDate);
                    break;
                case 'bi_annually':
                    $this->createBiAnnualInstances($module, $teachers, $currentYear, $currentDate);
                    break;
                case 'annually':
                    $this->createAnnualInstances($module, $teachers, $currentYear, $currentDate);
                    break;
            }
        }

        // Create instances linked to programme instances
        foreach ($programmeInstances as $programmeInstance) {
            if ($this->shouldCreateModuleInstanceForProgramme($module, $programmeInstance)) {
                $this->createProgrammeLinkedInstance($module, $teachers, $programmeInstance);
            }
        }
    }

    private function createMonthlyInstances(Module $module, $teachers, $currentYear, $currentDate)
    {
        // Create 6 months of instances (3 past, current, 2 future)
        for ($monthOffset = -3; $monthOffset <= 2; $monthOffset++) {
            $startDate = $currentDate->copy()->addMonths($monthOffset)->startOfMonth();
            $endDate = $startDate->copy()->addMonths(3); // 3-month duration

            ModuleInstance::create([
                'module_id' => $module->id,
                'tutor_user_id' => $teachers->random()->id,
                'start_date' => $startDate,
                'target_end_date' => $endDate,
                'programme_instance_id' => null,
                'delivery_style' => 'async',
            ]);
        }
    }

    private function createQuarterlyInstances(Module $module, $teachers, $currentYear, $currentDate)
    {
        $quarters = [
            ['start' => Carbon::create($currentYear, 1, 1), 'duration' => 4],
            ['start' => Carbon::create($currentYear, 4, 1), 'duration' => 4],
            ['start' => Carbon::create($currentYear, 7, 1), 'duration' => 4],
            ['start' => Carbon::create($currentYear, 10, 1), 'duration' => 4],
        ];

        // Add previous year Q4 for historical data
        $quarters[] = ['start' => Carbon::create($currentYear - 1, 10, 1), 'duration' => 4];

        foreach ($quarters as $quarter) {
            $startDate = $quarter['start'];
            $endDate = $startDate->copy()->addMonths($quarter['duration']);

            ModuleInstance::create([
                'module_id' => $module->id,
                'tutor_user_id' => $teachers->random()->id,
                'start_date' => $startDate,
                'target_end_date' => $endDate,
                'programme_instance_id' => null,
                'delivery_style' => 'async',
            ]);
        }
    }

    private function createBiAnnualInstances(Module $module, $teachers, $currentYear, $currentDate)
    {
        $periods = [
            ['start' => Carbon::create($currentYear, 1, 1), 'duration' => 6],
            ['start' => Carbon::create($currentYear, 7, 1), 'duration' => 6],
            ['start' => Carbon::create($currentYear - 1, 7, 1), 'duration' => 6], // Previous period
        ];

        foreach ($periods as $period) {
            $startDate = $period['start'];
            $endDate = $startDate->copy()->addMonths($period['duration']);

            ModuleInstance::create([
                'module_id' => $module->id,
                'tutor_user_id' => $teachers->random()->id,
                'start_date' => $startDate,
                'target_end_date' => $endDate,
                'programme_instance_id' => null,
                'delivery_style' => 'async',
            ]);
        }
    }

    private function createAnnualInstances(Module $module, $teachers, $currentYear, $currentDate)
    {
        $years = [$currentYear - 1, $currentYear, $currentYear + 1];

        foreach ($years as $year) {
            $startDate = Carbon::create($year, 9, 1); // Academic year start
            $endDate = $startDate->copy()->addYear();

            ModuleInstance::create([
                'module_id' => $module->id,
                'tutor_user_id' => $teachers->random()->id,
                'start_date' => $startDate,
                'target_end_date' => $endDate,
                'programme_instance_id' => null,
                'delivery_style' => 'async',
            ]);
        }
    }

    private function shouldCreateModuleInstanceForProgramme(Module $module, ProgrammeInstance $programmeInstance): bool
    {
        // Business Management modules for Business programmes
        if (str_contains($module->code, 'BM') && str_contains($programmeInstance->programme->title, 'Business')) {
            return true;
        }

        // IT modules for IT programmes
        if (str_contains($module->code, 'IT') && str_contains($programmeInstance->programme->title, 'Information Technology')) {
            return true;
        }

        // Digital Marketing modules for Digital Marketing programmes
        if (str_contains($module->code, 'DM') && str_contains($programmeInstance->programme->title, 'Digital Marketing')) {
            return true;
        }

        // Data Analytics modules for Data Analytics programmes
        if (str_contains($module->code, 'DA') && str_contains($programmeInstance->programme->title, 'Data Analytics')) {
            return true;
        }

        // General modules (GEN, PM, DL) for all programmes
        if (in_array(substr($module->code, 0, 3), ['GEN', 'PM', 'DL'])) {
            return true;
        }

        // Cybersecurity for both IT and Cybersecurity programmes
        if (str_contains($module->title, 'Cybersecurity') &&
            (str_contains($programmeInstance->programme->title, 'Information Technology') ||
             str_contains($programmeInstance->programme->title, 'Cybersecurity'))) {
            return true;
        }

        return false;
    }

    private function createProgrammeLinkedInstance(Module $module, $teachers, ProgrammeInstance $programmeInstance)
    {
        // Calculate start and end dates based on programme instance
        $startDate = $programmeInstance->intake_start_date->copy();

        // Stagger module starts throughout the programme
        $moduleOffset = crc32($module->code.$programmeInstance->id) % 12; // Pseudo-random but consistent
        $startDate->addMonths($moduleOffset);

        $duration = match ($module->credits) {
            5 => 2,   // 2 months for 5 credit modules
            10 => 3,  // 3 months for 10 credit modules
            15 => 4,  // 4 months for 15 credit modules
            20 => 5,  // 5 months for 20 credit modules
            default => 3
        };

        $endDate = $startDate->copy()->addMonths($duration);

        // Select appropriate tutor based on module subject area
        $tutor = $this->selectAppropriateTeacher($module, $teachers);

        ModuleInstance::create([
            'module_id' => $module->id,
            'tutor_user_id' => $tutor->id,
            'start_date' => $startDate,
            'target_end_date' => $endDate,
            'programme_instance_id' => $programmeInstance->id,
            'delivery_style' => $programmeInstance->default_delivery_style,
        ]);
    }

    private function selectAppropriateTeacher(Module $module, $teachers)
    {
        $subjectTeachers = [
            'business' => ['michael.ryan@theopencollege.ie', 'sarah.kennedy@theopencollege.ie', 'mark.thompson@theopencollege.ie', 'jennifer.clarke@theopencollege.ie', 'robert.fitzgerald@theopencollege.ie'],
            'it' => ['alan.mccarthy@theopencollege.ie', 'catherine.osullivan@theopencollege.ie', 'daniel.hughes@theopencollege.ie', 'michelle.oconnor@theopencollege.ie', 'kevin.byrne@theopencollege.ie'],
            'marketing' => ['sophie.walsh@theopencollege.ie', 'andrew.kelly@theopencollege.ie', 'rachel.murphy@theopencollege.ie'],
            'analytics' => ['thomas.oreilly@theopencollege.ie', 'laura.doyle@theopencollege.ie', 'stephen.collins@theopencollege.ie'],
            'general' => ['mary.odonnell@theopencollege.ie', 'paul.mcgrath@theopencollege.ie', 'helen.casey@theopencollege.ie', 'brian.flanagan@theopencollege.ie'],
        ];

        $moduleCode = $module->code;
        $appropriateEmails = [];

        if (str_contains($moduleCode, 'BM')) {
            $appropriateEmails = $subjectTeachers['business'];
        } elseif (str_contains($moduleCode, 'IT')) {
            $appropriateEmails = $subjectTeachers['it'];
        } elseif (str_contains($moduleCode, 'DM')) {
            $appropriateEmails = $subjectTeachers['marketing'];
        } elseif (str_contains($moduleCode, 'DA')) {
            $appropriateEmails = $subjectTeachers['analytics'];
        } else {
            $appropriateEmails = $subjectTeachers['general'];
        }

        // Find teacher by email
        $appropriateTeachers = $teachers->whereIn('email', $appropriateEmails);

        return $appropriateTeachers->isNotEmpty() ? $appropriateTeachers->random() : $teachers->random();
    }
}
