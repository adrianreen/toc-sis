<?php

namespace Database\Seeders;

use App\Models\Programme;
use Illuminate\Database\Seeder;

class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $programmes = [
            [
                'title' => 'Bachelor of Arts in Business Management',
                'awarding_body' => 'The Open College',
                'nfq_level' => 7,
                'total_credits' => 180,
                'description' => 'A comprehensive business management programme covering key areas including marketing, finance, human resources, operations, and strategic management. Students develop practical skills in leadership, problem-solving, and critical thinking.',
                'learning_outcomes' => [
                    'Demonstrate comprehensive knowledge of business management principles and practices',
                    'Apply analytical and problem-solving skills to complex business scenarios',
                    'Communicate effectively in professional business environments',
                    'Evaluate ethical considerations in business decision-making',
                    'Develop and implement strategic business plans',
                ],
            ],
            [
                'title' => 'Bachelor of Science in Information Technology',
                'awarding_body' => 'The Open College',
                'nfq_level' => 7,
                'total_credits' => 180,
                'description' => 'A modern IT programme covering software development, database management, networking, cybersecurity, and emerging technologies. Emphasis on practical skills and industry-relevant knowledge.',
                'learning_outcomes' => [
                    'Design and develop software applications using modern programming languages',
                    'Implement and manage database systems effectively',
                    'Configure and maintain network infrastructure and security systems',
                    'Apply project management methodologies to IT projects',
                    'Evaluate and recommend appropriate technology solutions for business needs',
                ],
            ],
            [
                'title' => 'Bachelor of Arts in Digital Marketing',
                'awarding_body' => 'The Open College',
                'nfq_level' => 7,
                'total_credits' => 180,
                'description' => 'A cutting-edge digital marketing programme focusing on social media marketing, content creation, SEO/SEM, analytics, and e-commerce strategies. Students learn to navigate the digital landscape effectively.',
                'learning_outcomes' => [
                    'Develop comprehensive digital marketing campaigns across multiple channels',
                    'Analyze and interpret digital marketing metrics and analytics',
                    'Create engaging content for various digital platforms',
                    'Implement SEO and SEM strategies to improve online visibility',
                    'Design and optimize e-commerce customer experiences',
                ],
            ],
            [
                'title' => 'Bachelor of Science in Data Analytics',
                'awarding_body' => 'The Open College',
                'nfq_level' => 7,
                'total_credits' => 180,
                'description' => 'A specialized programme in data analytics covering statistical analysis, machine learning, data visualization, and big data technologies. Students learn to extract insights from complex datasets.',
                'learning_outcomes' => [
                    'Apply statistical methods and machine learning algorithms to analyze data',
                    'Create compelling data visualizations and dashboards',
                    'Work with big data technologies and cloud computing platforms',
                    'Communicate data insights effectively to stakeholders',
                    'Ensure data quality, privacy, and ethical use of information',
                ],
            ],
            [
                'title' => 'Higher Certificate in Business Studies',
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 120,
                'description' => 'An introductory business programme providing foundational knowledge in business operations, basic accounting, marketing principles, and professional communication skills.',
                'learning_outcomes' => [
                    'Understand fundamental business concepts and terminology',
                    'Perform basic accounting and financial calculations',
                    'Apply basic marketing principles to small business scenarios',
                    'Demonstrate professional communication skills',
                    'Work effectively in team environments',
                ],
            ],
            [
                'title' => 'Higher Certificate in Computing',
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 120,
                'description' => 'A foundational computing programme covering programming basics, computer systems, web development, and database fundamentals. Ideal for career changers and new entrants to IT.',
                'learning_outcomes' => [
                    'Write basic programs using contemporary programming languages',
                    'Understand computer hardware and software systems',
                    'Create simple websites using HTML, CSS, and JavaScript',
                    'Design and query basic database systems',
                    'Apply problem-solving techniques to computing challenges',
                ],
            ],
            [
                'title' => 'Master of Business Administration (MBA)',
                'awarding_body' => 'The Open College',
                'nfq_level' => 9,
                'total_credits' => 90,
                'description' => 'An advanced business administration programme for experienced professionals seeking senior management roles. Covers strategic management, leadership, finance, and global business perspectives.',
                'learning_outcomes' => [
                    'Formulate and implement strategic business decisions',
                    'Lead and manage diverse teams effectively',
                    'Analyze complex financial and operational data',
                    'Navigate international business environments',
                    'Drive organizational change and innovation',
                ],
            ],
            [
                'title' => 'Master of Science in Cybersecurity',
                'awarding_body' => 'The Open College',
                'nfq_level' => 9,
                'total_credits' => 90,
                'description' => 'An advanced cybersecurity programme covering threat analysis, security architecture, incident response, and regulatory compliance. Designed for IT professionals seeking cybersecurity expertise.',
                'learning_outcomes' => [
                    'Design and implement comprehensive security architectures',
                    'Conduct advanced threat analysis and vulnerability assessments',
                    'Manage cybersecurity incidents and business continuity',
                    'Ensure compliance with cybersecurity regulations and standards',
                    'Lead cybersecurity teams and strategic initiatives',
                ],
            ],
            [
                'title' => 'Certificate in Project Management',
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 30,
                'description' => 'A focused certificate programme covering project management fundamentals, methodologies, tools, and leadership skills. Aligned with PMI standards.',
                'learning_outcomes' => [
                    'Apply project management methodologies effectively',
                    'Use project management tools and software',
                    'Manage project teams and stakeholder relationships',
                    'Control project scope, time, and budget',
                    'Identify and mitigate project risks',
                ],
            ],
            [
                'title' => 'Certificate in Digital Skills',
                'awarding_body' => 'The Open College',
                'nfq_level' => 5,
                'total_credits' => 30,
                'description' => 'An essential digital skills programme covering computer literacy, internet safety, digital communication, and basic productivity software. Ideal for digital inclusion initiatives.',
                'learning_outcomes' => [
                    'Use computers and mobile devices confidently',
                    'Navigate the internet safely and effectively',
                    'Communicate using digital platforms and email',
                    'Create documents and presentations using productivity software',
                    'Understand digital privacy and security basics',
                ],
            ],
        ];

        foreach ($programmes as $programme) {
            Programme::create($programme);
        }
    }
}
