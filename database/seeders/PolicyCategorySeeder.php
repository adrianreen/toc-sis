<?php

namespace Database\Seeders;

use App\Models\PolicyCategory;
use Illuminate\Database\Seeder;

class PolicyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Assessments',
                'description' => 'Assessment processes, submission guidelines, timelines and exam information',
                'sort_order' => 1,
            ],
            [
                'name' => 'eLearning Support Services',
                'description' => 'Academic writing guides, tutor contact information, and platform support',
                'sort_order' => 2,
            ],
            [
                'name' => 'Certification',
                'description' => 'Certification processes, accreditation information and certification dates',
                'sort_order' => 3,
            ],
            [
                'name' => 'QA Policies',
                'description' => 'Quality assurance policies including academic conduct, integrity and appeals',
                'sort_order' => 4,
            ],
            [
                'name' => 'Protection for Learners',
                'description' => 'Student privacy, protection policies and charter information',
                'sort_order' => 5,
            ],
            [
                'name' => 'Degree Programmes',
                'description' => 'Policies specific to degree programmes including admissions and assessments',
                'sort_order' => 6,
            ],
            [
                'name' => 'ELC Programmes',
                'description' => 'Policies specific to ELC programmes including assessments and circumstances',
                'sort_order' => 7,
            ],
            [
                'name' => 'QQI Programmes',
                'description' => 'Policies specific to QQI programmes and certifications',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            PolicyCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}