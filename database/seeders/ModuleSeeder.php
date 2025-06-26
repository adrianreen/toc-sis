<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // Business Management Modules
            [
                'title' => 'Introduction to Business Management',
                'module_code' => 'BM101',
                'credit_value' => 10,
                'learning_outcomes' => [
                    'Identify key business management functions and processes',
                    'Analyze different organizational structures and their effectiveness',
                    'Apply basic management principles to workplace scenarios',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Individual Report',
                        'weighting' => 40,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Final Examination',
                        'weighting' => 60,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Strategic Management',
                'code' => 'BM301',
                'credits' => 15,
                'description' => 'Advanced module focusing on strategic planning, competitive analysis, and strategic implementation in modern organizations.',
                'learning_outcomes' => [
                    'Formulate comprehensive strategic plans for organizations',
                    'Conduct detailed competitive and market analysis',
                    'Evaluate strategic options using appropriate frameworks',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Strategic Analysis Report',
                        'weighting' => 50,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Group Presentation',
                        'weighting' => 30,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Reflective Essay',
                        'weighting' => 20,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => false,
                'async_instance_cadence' => 'quarterly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Financial Management',
                'code' => 'BM205',
                'credits' => 15,
                'description' => 'Comprehensive coverage of financial planning, analysis, and decision-making in business contexts.',
                'learning_outcomes' => [
                    'Analyze financial statements and performance indicators',
                    'Prepare budgets and financial forecasts',
                    'Evaluate investment opportunities and financing decisions',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Financial Analysis Assignment',
                        'weighting' => 45,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Final Examination',
                        'weighting' => 55,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],

            // IT Modules
            [
                'title' => 'Programming Fundamentals',
                'code' => 'IT101',
                'credits' => 15,
                'description' => 'Introduction to programming concepts using modern languages, covering syntax, logic, and problem-solving approaches.',
                'learning_outcomes' => [
                    'Write basic programs using structured programming principles',
                    'Apply debugging and testing techniques',
                    'Solve computational problems using algorithmic thinking',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Programming Portfolio',
                        'weighting' => 60,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Practical Examination',
                        'weighting' => 40,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Database Systems',
                'code' => 'IT205',
                'credits' => 15,
                'description' => 'Comprehensive study of database design, implementation, and management using SQL and modern database systems.',
                'learning_outcomes' => [
                    'Design normalized relational database schemas',
                    'Write complex SQL queries for data retrieval and manipulation',
                    'Implement database security and performance optimization',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Database Design Project',
                        'weighting' => 50,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'SQL Skills Test',
                        'weighting' => 35,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Technical Report',
                        'weighting' => 15,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Cybersecurity Fundamentals',
                'code' => 'IT301',
                'credits' => 15,
                'description' => 'Essential cybersecurity concepts covering threat analysis, risk management, and security controls implementation.',
                'learning_outcomes' => [
                    'Identify and assess cybersecurity threats and vulnerabilities',
                    'Implement appropriate security controls and countermeasures',
                    'Develop incident response and recovery procedures',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Security Assessment Report',
                        'weighting' => 40,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Practical Security Lab',
                        'weighting' => 35,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Final Examination',
                        'weighting' => 25,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'quarterly',
                'default_pass_mark' => 40,
            ],

            // Digital Marketing Modules
            [
                'title' => 'Digital Marketing Strategy',
                'code' => 'DM101',
                'credits' => 15,
                'description' => 'Comprehensive introduction to digital marketing channels, strategies, and campaign development.',
                'learning_outcomes' => [
                    'Develop integrated digital marketing campaigns',
                    'Analyze digital marketing performance metrics',
                    'Select appropriate digital channels for target audiences',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Digital Campaign Project',
                        'weighting' => 60,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Analytics Report',
                        'weighting' => 25,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Peer Review Exercise',
                        'weighting' => 15,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Social Media Marketing',
                'code' => 'DM205',
                'credits' => 10,
                'description' => 'Specialized module focusing on social media platforms, content creation, and community management.',
                'learning_outcomes' => [
                    'Create engaging content for multiple social media platforms',
                    'Develop social media strategies aligned with business objectives',
                    'Measure and optimize social media campaign performance',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Social Media Campaign',
                        'weighting' => 70,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Content Portfolio',
                        'weighting' => 30,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],

            // Data Analytics Modules
            [
                'title' => 'Statistical Analysis',
                'code' => 'DA101',
                'credits' => 15,
                'description' => 'Fundamental statistical concepts and methods for data analysis, including descriptive and inferential statistics.',
                'learning_outcomes' => [
                    'Apply appropriate statistical methods to analyze datasets',
                    'Interpret statistical results and their business implications',
                    'Use statistical software for data analysis tasks',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Statistical Analysis Project',
                        'weighting' => 55,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Mid-term Examination',
                        'weighting' => 25,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Final Examination',
                        'weighting' => 20,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Machine Learning Applications',
                'code' => 'DA301',
                'credits' => 20,
                'description' => 'Advanced module covering machine learning algorithms, model development, and practical applications in business contexts.',
                'learning_outcomes' => [
                    'Implement machine learning algorithms for predictive modeling',
                    'Evaluate and optimize model performance',
                    'Apply machine learning solutions to real-world business problems',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Machine Learning Project',
                        'weighting' => 65,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Technical Presentation',
                        'weighting' => 20,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Peer Code Review',
                        'weighting' => 15,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => false,
                'async_instance_cadence' => 'quarterly',
                'default_pass_mark' => 40,
            ],

            // Foundation/Certificate Modules
            [
                'title' => 'Professional Communication',
                'code' => 'GEN101',
                'credits' => 5,
                'description' => 'Essential communication skills for professional environments, including written and oral communication.',
                'learning_outcomes' => [
                    'Communicate effectively in professional settings',
                    'Write clear and concise business documents',
                    'Deliver effective presentations to diverse audiences',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Written Communication Portfolio',
                        'weighting' => 50,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Oral Presentation',
                        'weighting' => 50,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Research Methods',
                'code' => 'GEN201',
                'credits' => 10,
                'description' => 'Introduction to research methodologies, data collection techniques, and academic writing standards.',
                'learning_outcomes' => [
                    'Design appropriate research methodologies',
                    'Collect and analyze research data effectively',
                    'Present research findings in academic formats',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Research Proposal',
                        'weighting' => 30,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Research Project',
                        'weighting' => 70,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'quarterly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Project Management Fundamentals',
                'code' => 'PM101',
                'credits' => 10,
                'description' => 'Essential project management concepts, methodologies, and tools for successful project delivery.',
                'learning_outcomes' => [
                    'Apply project management methodologies to real projects',
                    'Use project management tools and software effectively',
                    'Manage project stakeholders and communications',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Project Management Plan',
                        'weighting' => 60,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Practical Skills Test',
                        'weighting' => 40,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
            [
                'title' => 'Digital Literacy',
                'code' => 'DL101',
                'credits' => 5,
                'description' => 'Fundamental digital skills including computer operations, internet usage, and basic software applications.',
                'learning_outcomes' => [
                    'Use computers and mobile devices confidently',
                    'Navigate internet resources safely and effectively',
                    'Create documents using common productivity software',
                ],
                'assessment_strategy' => [
                    [
                        'component_name' => 'Practical Skills Assessment',
                        'weighting' => 60,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                    [
                        'component_name' => 'Digital Portfolio',
                        'weighting' => 40,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'monthly',
                'default_pass_mark' => 40,
            ],
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
