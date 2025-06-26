<?php

namespace Database\Seeders;

use App\Models\Programme;
use App\Models\ProgrammeInstance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProgrammeInstanceSeeder extends Seeder
{
    public function run(): void
    {
        $programmes = Programme::all();

        foreach ($programmes as $programme) {
            // Create multiple instances for each programme
            $this->createProgrammeInstances($programme);
        }
    }

    private function createProgrammeInstances(Programme $programme): void
    {
        $currentYear = Carbon::now()->year;
        $instances = [];

        // Bachelor's and Master's programmes - multiple cohorts per year
        if (in_array($programme->nfq_level, [7, 9])) {
            $instances = [
                [
                    'label' => "September {$currentYear} Intake",
                    'intake_start_date' => Carbon::create($currentYear, 9, 1),
                    'intake_end_date' => Carbon::create($currentYear, 9, 30),
                    'default_delivery_style' => 'sync',
                ],
                [
                    'label' => 'January '.($currentYear + 1).' Intake',
                    'intake_start_date' => Carbon::create($currentYear + 1, 1, 15),
                    'intake_end_date' => Carbon::create($currentYear + 1, 2, 15),
                    'default_delivery_style' => 'sync',
                ],
                [
                    'label' => "{$currentYear} Rolling Enrolment",
                    'intake_start_date' => Carbon::create($currentYear, 1, 1),
                    'intake_end_date' => Carbon::create($currentYear + 1, 12, 31),
                    'default_delivery_style' => 'async',
                ],
            ];

            // Add previous year instances for historical data
            $instances[] = [
                'label' => 'September '.($currentYear - 1).' Intake',
                'intake_start_date' => Carbon::create($currentYear - 1, 9, 1),
                'intake_end_date' => Carbon::create($currentYear - 1, 9, 30),
                'default_delivery_style' => 'sync',
            ];
        }

        // Higher Certificates - more frequent intakes
        elseif ($programme->nfq_level == 6) {
            $instances = [
                [
                    'label' => "September {$currentYear} Cohort",
                    'intake_start_date' => Carbon::create($currentYear, 9, 1),
                    'intake_end_date' => Carbon::create($currentYear, 9, 15),
                    'default_delivery_style' => 'sync',
                ],
                [
                    'label' => "January {$currentYear} Cohort",
                    'intake_start_date' => Carbon::create($currentYear, 1, 15),
                    'intake_end_date' => Carbon::create($currentYear, 1, 31),
                    'default_delivery_style' => 'sync',
                ],
                [
                    'label' => "May {$currentYear} Cohort",
                    'intake_start_date' => Carbon::create($currentYear, 5, 1),
                    'intake_end_date' => Carbon::create($currentYear, 5, 15),
                    'default_delivery_style' => 'sync',
                ],
                [
                    'label' => "{$currentYear} Flexible Learning",
                    'intake_start_date' => Carbon::create($currentYear, 1, 1),
                    'intake_end_date' => Carbon::create($currentYear + 1, 12, 31),
                    'default_delivery_style' => 'async',
                ],
            ];
        }

        // Certificates - rolling/frequent intake
        elseif (in_array($programme->nfq_level, [5, 6]) && str_contains($programme->title, 'Certificate')) {
            $instances = [
                [
                    'label' => "Monthly Intake {$currentYear}",
                    'intake_start_date' => Carbon::create($currentYear, 1, 1),
                    'intake_end_date' => Carbon::create($currentYear + 1, 12, 31),
                    'default_delivery_style' => 'async',
                ],
                [
                    'label' => "October {$currentYear} Evening Cohort",
                    'intake_start_date' => Carbon::create($currentYear, 10, 1),
                    'intake_end_date' => Carbon::create($currentYear, 10, 15),
                    'default_delivery_style' => 'sync',
                ],
                [
                    'label' => "February {$currentYear} Weekend Cohort",
                    'intake_start_date' => Carbon::create($currentYear, 2, 1),
                    'intake_end_date' => Carbon::create($currentYear, 2, 15),
                    'default_delivery_style' => 'sync',
                ],
            ];
        }

        // Create the programme instances
        foreach ($instances as $instanceData) {
            ProgrammeInstance::create([
                'programme_id' => $programme->id,
                'label' => $instanceData['label'],
                'intake_start_date' => $instanceData['intake_start_date'],
                'intake_end_date' => $instanceData['intake_end_date'],
                'default_delivery_style' => $instanceData['default_delivery_style'],
            ]);
        }
    }
}
