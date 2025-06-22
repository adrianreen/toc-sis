#!/bin/bash

# TOC-SIS Test Data Generation Script
# This script generates comprehensive, realistic test data for workflow testing

set -e

echo "========================================="
echo "TOC-SIS Test Data Generation"
echo "========================================="

# Configuration
DATA_SIZE="${DATA_SIZE:-medium}"  # small, medium, large, bulk
INCLUDE_HISTORICAL="${INCLUDE_HISTORICAL:-true}"
INCLUDE_PROBLEMATIC="${INCLUDE_PROBLEMATIC:-true}"
RESET_DATABASE="${RESET_DATABASE:-false}"

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}$message${NC}"
}

# Function to show progress
show_progress() {
    local current=$1
    local total=$2
    local description="$3"
    local percentage=$((current * 100 / total))
    echo -ne "\r${BLUE}Progress: ${percentage}% - ${description}${NC}"
}

# Function to determine data volumes based on size parameter
get_data_volumes() {
    case "$DATA_SIZE" in
        "small")
            STUDENTS=50
            PROGRAMMES=3
            PROGRAMME_INSTANCES=6
            MODULES=8
            MODULE_INSTANCES=20
            ENROLMENTS=60
            STAFF_USERS=8
            ;;
        "medium")
            STUDENTS=200
            PROGRAMMES=5
            PROGRAMME_INSTANCES=12
            MODULES=15
            MODULE_INSTANCES=40
            ENROLMENTS=250
            STAFF_USERS=15
            ;;
        "large")
            STUDENTS=500
            PROGRAMMES=8
            PROGRAMME_INSTANCES=20
            MODULES=25
            MODULE_INSTANCES=80
            ENROLMENTS=600
            STAFF_USERS=25
            ;;
        "bulk")
            STUDENTS=1000
            PROGRAMMES=12
            PROGRAMME_INSTANCES=30
            MODULES=40
            MODULE_INSTANCES=150
            ENROLMENTS=1200
            STAFF_USERS=40
            ;;
        *)
            echo "Invalid data size. Use: small, medium, large, or bulk"
            exit 1
            ;;
    esac
    
    print_status "$BLUE" "Data size: $DATA_SIZE"
    print_status "$BLUE" "Target volumes: $STUDENTS students, $PROGRAMMES programmes, $MODULE_INSTANCES module instances"
}

# Function to reset database if requested
reset_database() {
    if [ "$RESET_DATABASE" = "true" ]; then
        print_status "$YELLOW" "=== Resetting Database ==="
        
        echo "Dropping all tables and recreating..."
        php artisan migrate:fresh --force
        
        echo "Running base seeders..."
        php artisan db:seed --class=UserSeeder
        php artisan db:seed --class=EmailTemplateSeeder
        
        print_status "$GREEN" "✅ Database reset completed"
    fi
}

# Function to generate realistic student data
generate_students() {
    print_status "$BLUE" "=== Generating Students ($STUDENTS) ==="
    
    php artisan tinker --execute="
        \$irish_first_names = [
            'Aiden', 'Aoife', 'Cian', 'Ciara', 'Conor', 'Emma', 'Fionn', 'Grace',
            'Jack', 'Kate', 'Liam', 'Lucy', 'Michael', 'Niamh', 'Oisin', 'Sarah',
            'Sean', 'Saoirse', 'Thomas', 'Zoe', 'Darragh', 'Ella', 'Eoin', 'Hannah',
            'James', 'Lily', 'Noah', 'Sophie', 'Adam', 'Amelia', 'Cillian', 'Emily',
            'Daniel', 'Faye', 'Finn', 'Isabella', 'Luke', 'Mia', 'Ryan', 'Anna'
        ];
        
        \$irish_surnames = [
            'Murphy', 'Kelly', 'O\'Sullivan', 'Walsh', 'Smith', 'O\'Brien', 'Byrne',
            'O\'Connor', 'Ryan', 'O\'Neill', 'O\'Reilly', 'Doyle', 'McCarthy',
            'Gallagher', 'O\'Doherty', 'Kennedy', 'Lynch', 'Murray', 'Quinn',
            'Moore', 'McLoughlin', 'O\'Carroll', 'Connolly', 'Daly', 'O\'Connell',
            'Wilson', 'Dunne', 'Clarke', 'Flanagan', 'Nolan', 'Power', 'Healy',
            'O\'Shea', 'White', 'Kavanagh', 'McGrath', 'Maguire', 'O\'Mahony',
            'McDonnell', 'O\'Farrell'
        ];
        
        \$irish_cities = [
            'Dublin', 'Cork', 'Galway', 'Limerick', 'Waterford', 'Kilkenny',
            'Sligo', 'Athlone', 'Tralee', 'Wexford', 'Drogheda', 'Dundalk',
            'Navan', 'Ennis', 'Carlow', 'Naas', 'Letterkenny', 'Tullamore',
            'Newbridge', 'Portlaoise', 'Bray', 'Clonmel', 'Mullingar'
        ];
        
        \$student_statuses = ['enquiry', 'enrolled', 'active', 'active', 'active']; // Weight towards active
        
        \$international_students = [
            ['first' => 'Ahmed', 'last' => 'Hassan', 'city' => 'Dublin'],
            ['first' => 'Maria', 'last' => 'Rodriguez', 'city' => 'Cork'],
            ['first' => 'Chen', 'last' => 'Wei', 'city' => 'Galway'],
            ['first' => 'Pierre', 'last' => 'Dubois', 'city' => 'Dublin'],
            ['first' => 'Anna', 'last' => 'Kowalski', 'city' => 'Limerick'],
            ['first' => 'Raj', 'last' => 'Patel', 'city' => 'Cork'],
            ['first' => 'Elena', 'last' => 'Volkov', 'city' => 'Dublin'],
            ['first' => 'Hassan', 'last' => 'Al-Rashid', 'city' => 'Galway']
        ];
        
        \$created_count = 0;
        \$target_count = $STUDENTS;
        
        // Create diverse student population
        for (\$i = 0; \$i < \$target_count; \$i++) {
            // 15% international students
            if (\$i < count(\$international_students) && rand(1, 100) <= 15) {
                \$student_data = \$international_students[\$i % count(\$international_students)];
                \$first_name = \$student_data['first'];
                \$last_name = \$student_data['last'];
                \$city = \$student_data['city'];
            } else {
                \$first_name = \$irish_first_names[array_rand(\$irish_first_names)];
                \$last_name = \$irish_surnames[array_rand(\$irish_surnames)];
                \$city = \$irish_cities[array_rand(\$irish_cities)];
            }
            
            \$status = \$student_statuses[array_rand(\$student_statuses)];
            
            // Generate realistic age distribution
            \$age_group = rand(1, 100);
            if (\$age_group <= 60) {
                // Traditional students (18-25)
                \$birth_year = rand(1998, 2005);
            } elseif (\$age_group <= 85) {
                // Mature students (26-40)
                \$birth_year = rand(1983, 1997);
            } else {
                // Older adult learners (41-55)
                \$birth_year = rand(1968, 1982);
            }
            
            try {
                \$student = App\Models\Student::create([
                    'student_number' => App\Models\Student::generateStudentNumber(),
                    'first_name' => \$first_name,
                    'last_name' => \$last_name,
                    'email' => strtolower(\$first_name . '.' . str_replace(\"'\", '', \$last_name) . (\$i + 1000) . '@student.ie'),
                    'phone' => '085' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'address' => rand(1, 999) . ' ' . ['Main', 'High', 'Church', 'Bridge', 'Castle', 'Park', 'Hill', 'Green'][array_rand(['Main', 'High', 'Church', 'Bridge', 'Castle', 'Park', 'Hill', 'Green'])] . ' Street',
                    'city' => \$city,
                    'county' => \$city,
                    'eircode' => chr(rand(65, 90)) . rand(10, 99) . ' ' . chr(rand(65, 90)) . rand(100, 999),
                    'date_of_birth' => Carbon\Carbon::create(\$birth_year, rand(1, 12), rand(1, 28)),
                    'status' => \$status
                ]);
                
                \$created_count++;
                
                if (\$created_count % 50 === 0) {
                    echo 'Created ' . \$created_count . ' students...' . PHP_EOL;
                }
            } catch (Exception \$e) {
                echo 'Error creating student: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        echo 'Total students created: ' . \$created_count . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Students generated successfully"
}

# Function to generate staff users
generate_staff_users() {
    print_status "$BLUE" "=== Generating Staff Users ($STAFF_USERS) ==="
    
    php artisan tinker --execute="
        \$staff_names = [
            ['first' => 'Sarah', 'last' => 'O\'Brien', 'role' => 'manager'],
            ['first' => 'Michael', 'last' => 'Walsh', 'role' => 'manager'],
            ['first' => 'Emma', 'last' => 'Kelly', 'role' => 'student_services'],
            ['first' => 'James', 'last' => 'Murphy', 'role' => 'student_services'],
            ['first' => 'Lisa', 'last' => 'Ryan', 'role' => 'student_services'],
            ['first' => 'David', 'last' => 'O\'Connor', 'role' => 'teacher'],
            ['first' => 'Mary', 'last' => 'Byrne', 'role' => 'teacher'],
            ['first' => 'John', 'last' => 'McCarthy', 'role' => 'teacher'],
            ['first' => 'Rachel', 'last' => 'Gallagher', 'role' => 'teacher'],
            ['first' => 'Paul', 'last' => 'Kennedy', 'role' => 'teacher'],
            ['first' => 'Claire', 'last' => 'Doyle', 'role' => 'teacher'],
            ['first' => 'Kevin', 'last' => 'Lynch', 'role' => 'teacher'],
            ['first' => 'Susan', 'last' => 'Quinn', 'role' => 'teacher'],
            ['first' => 'Brian', 'last' => 'Moore', 'role' => 'teacher'],
            ['first' => 'Helen', 'last' => 'Clarke', 'role' => 'teacher']
        ];
        
        \$created_count = 0;
        \$target_count = min($STAFF_USERS, count(\$staff_names));
        
        for (\$i = 0; \$i < \$target_count; \$i++) {
            \$staff = \$staff_names[\$i];
            
            try {
                \$user = App\Models\User::firstOrCreate([
                    'email' => strtolower(\$staff['first'] . '.' . str_replace(\"'\", '', \$staff['last']) . '@theopencollege.com')
                ], [
                    'name' => \$staff['first'] . ' ' . \$staff['last'],
                    'role' => \$staff['role'],
                    'password' => Hash::make('password123'),
                ]);
                
                \$created_count++;
            } catch (Exception \$e) {
                echo 'Error creating staff user: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        echo 'Staff users created: ' . \$created_count . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Staff users generated successfully"
}

# Function to generate realistic programme structure
generate_programmes() {
    print_status "$BLUE" "=== Generating Programmes and Instances ==="
    
    php artisan tinker --execute="
        \$programme_templates = [
            [
                'title' => 'Bachelor of Arts in Business Management',
                'awarding_body' => 'The Open College',
                'nfq_level' => 8,
                'total_credits' => 180,
                'description' => 'A comprehensive undergraduate business management degree covering strategic management, finance, marketing, and operations.',
                'learning_outcomes' => 'Graduates will demonstrate advanced knowledge of business principles, strategic thinking, leadership capabilities, and ethical decision-making in complex business environments.',
                'delivery_styles' => ['sync']
            ],
            [
                'title' => 'Diploma in Digital Marketing',
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 60,
                'description' => 'Professional diploma focusing on digital marketing strategies, social media marketing, SEO, and online analytics.',
                'learning_outcomes' => 'Students will master digital marketing tools, develop comprehensive online marketing campaigns, and analyze digital marketing effectiveness.',
                'delivery_styles' => ['async']
            ],
            [
                'title' => 'Master of Science in Data Analytics',
                'awarding_body' => 'The Open College',
                'nfq_level' => 9,
                'total_credits' => 90,
                'description' => 'Advanced postgraduate programme in data analytics, machine learning, and business intelligence.',
                'learning_outcomes' => 'Graduates will demonstrate expertise in data analysis, statistical modeling, machine learning algorithms, and data-driven decision making.',
                'delivery_styles' => ['sync']
            ],
            [
                'title' => 'Certificate in Project Management',
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 30,
                'description' => 'Professional certificate in project management methodologies, tools, and best practices.',
                'learning_outcomes' => 'Students will apply project management principles, use project management software, and lead successful project teams.',
                'delivery_styles' => ['async']
            ],
            [
                'title' => 'Diploma in Human Resource Management',
                'awarding_body' => 'The Open College',
                'nfq_level' => 7,
                'total_credits' => 60,
                'description' => 'Professional diploma covering HR strategy, employment law, recruitment, and performance management.',
                'learning_outcomes' => 'Graduates will demonstrate competency in HR practices, legal compliance, strategic HR planning, and employee relations.',
                'delivery_styles' => ['sync']
            ],
            [
                'title' => 'Certificate in Cyber Security Fundamentals',
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 30,
                'description' => 'Essential cybersecurity knowledge for modern digital environments.',
                'learning_outcomes' => 'Students will understand security threats, implement protection measures, and respond to security incidents.',
                'delivery_styles' => ['async', 'sync']
            ],
            [
                'title' => 'Bachelor of Science in Software Development',
                'awarding_body' => 'The Open College',
                'nfq_level' => 8,
                'total_credits' => 180,
                'description' => 'Comprehensive software development programme covering programming, web development, and software engineering.',
                'learning_outcomes' => 'Graduates will design, develop, and deploy software solutions using modern programming languages and frameworks.',
                'delivery_styles' => ['sync']
            ],
            [
                'title' => 'Diploma in Financial Services',
                'awarding_body' => 'The Open College',
                'nfq_level' => 7,
                'total_credits' => 60,
                'description' => 'Professional qualification in financial services, banking, and investment management.',
                'learning_outcomes' => 'Students will understand financial markets, banking operations, and investment strategies.',
                'delivery_styles' => ['async']
            ]
        ];
        
        \$programmes_created = 0;
        \$instances_created = 0;
        \$target_programmes = min($PROGRAMMES, count(\$programme_templates));
        
        for (\$i = 0; \$i < \$target_programmes; \$i++) {
            \$template = \$programme_templates[\$i];
            
            try {
                \$programme = App\Models\Programme::create([
                    'title' => \$template['title'],
                    'awarding_body' => \$template['awarding_body'],
                    'nfq_level' => \$template['nfq_level'],
                    'total_credits' => \$template['total_credits'],
                    'description' => \$template['description'],
                    'learning_outcomes' => \$template['learning_outcomes']
                ]);
                
                \$programmes_created++;
                
                // Create programme instances for this programme
                \$delivery_styles = \$template['delivery_styles'];
                \$instances_per_programme = max(1, intval($PROGRAMME_INSTANCES / $PROGRAMMES));
                
                for (\$j = 0; \$j < \$instances_per_programme; \$j++) {
                    \$delivery_style = \$delivery_styles[array_rand(\$delivery_styles)];
                    
                    // Generate realistic intake dates
                    \$year = rand(2024, 2026);
                    if (\$delivery_style === 'sync') {
                        // Synchronous programmes start in Sep or Jan
                        \$month = rand(1, 2) === 1 ? 9 : 1;
                        \$start_date = Carbon\Carbon::create(\$year, \$month, 1);
                        \$end_date = \$start_date->copy()->addYears(2);
                        \$label = \$month === 9 ? 'September ' . \$year . ' Intake' : 'January ' . \$year . ' Intake';
                    } else {
                        // Asynchronous programmes have rolling enrollment
                        \$start_date = Carbon\Carbon::create(\$year, 1, 1);
                        \$end_date = Carbon\Carbon::create(\$year, 12, 31);
                        \$label = \$year . ' Rolling Enrolment';
                    }
                    
                    \$instance = App\Models\ProgrammeInstance::create([
                        'programme_id' => \$programme->id,
                        'label' => \$label,
                        'intake_start_date' => \$start_date,
                        'intake_end_date' => \$end_date,
                        'default_delivery_style' => \$delivery_style
                    ]);
                    
                    \$instances_created++;
                }
                
            } catch (Exception \$e) {
                echo 'Error creating programme: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        echo 'Programmes created: ' . \$programmes_created . PHP_EOL;
        echo 'Programme instances created: ' . \$instances_created . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Programmes and instances generated successfully"
}

# Function to generate modules with realistic assessment strategies
generate_modules() {
    print_status "$BLUE" "=== Generating Modules and Instances ==="
    
    php artisan tinker --execute="
        \$module_templates = [
            // Business modules
            [
                'title' => 'Strategic Management',
                'code' => 'BUS401',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Strategic Analysis Report', 'weighting' => 40, 'is_must_pass' => false],
                    ['component_name' => 'Case Study Presentation', 'weighting' => 30, 'is_must_pass' => false],
                    ['component_name' => 'Final Examination', 'weighting' => 30, 'is_must_pass' => true, 'component_pass_mark' => 40]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Marketing Fundamentals',
                'code' => 'MKT101',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Marketing Plan Project', 'weighting' => 70, 'is_must_pass' => false],
                    ['component_name' => 'Consumer Behavior Analysis', 'weighting' => 30, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'monthly'
            ],
            [
                'title' => 'Financial Management',
                'code' => 'FIN201',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Financial Analysis Project', 'weighting' => 50, 'is_must_pass' => false],
                    ['component_name' => 'Portfolio Management Exercise', 'weighting' => 25, 'is_must_pass' => false],
                    ['component_name' => 'Written Examination', 'weighting' => 25, 'is_must_pass' => true, 'component_pass_mark' => 40]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Operations Management',
                'code' => 'OPS301',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Process Improvement Project', 'weighting' => 60, 'is_must_pass' => false],
                    ['component_name' => 'Supply Chain Analysis', 'weighting' => 40, 'is_must_pass' => false]
                ],
                'standalone' => false,
                'cadence' => 'quarterly'
            ],
            
            // Digital Marketing modules
            [
                'title' => 'Social Media Marketing',
                'code' => 'DIG201',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Social Media Campaign', 'weighting' => 60, 'is_must_pass' => false],
                    ['component_name' => 'Analytics Report', 'weighting' => 40, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'monthly'
            ],
            [
                'title' => 'Search Engine Optimization',
                'code' => 'DIG301',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'SEO Audit Project', 'weighting' => 50, 'is_must_pass' => false],
                    ['component_name' => 'Keyword Strategy Plan', 'weighting' => 50, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'monthly'
            ],
            [
                'title' => 'Content Marketing Strategy',
                'code' => 'DIG401',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Content Strategy Document', 'weighting' => 100, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'monthly'
            ],
            
            // Data Analytics modules
            [
                'title' => 'Statistical Analysis',
                'code' => 'STA501',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Statistical Analysis Project', 'weighting' => 50, 'is_must_pass' => false],
                    ['component_name' => 'Research Methodology Report', 'weighting' => 30, 'is_must_pass' => false],
                    ['component_name' => 'Written Examination', 'weighting' => 20, 'is_must_pass' => true, 'component_pass_mark' => 40]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Machine Learning Applications',
                'code' => 'ML601',
                'credits' => 15,
                'assessment' => [
                    ['component_name' => 'ML Algorithm Implementation', 'weighting' => 40, 'is_must_pass' => false],
                    ['component_name' => 'Data Science Portfolio', 'weighting' => 40, 'is_must_pass' => false],
                    ['component_name' => 'Viva Voce Examination', 'weighting' => 20, 'is_must_pass' => true, 'component_pass_mark' => 50]
                ],
                'standalone' => false,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Database Management Systems',
                'code' => 'DBS401',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Database Design Project', 'weighting' => 60, 'is_must_pass' => false],
                    ['component_name' => 'SQL Programming Assessment', 'weighting' => 40, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            
            // Project Management modules
            [
                'title' => 'Project Planning and Control',
                'code' => 'PMP101',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Project Plan Development', 'weighting' => 100, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Risk Management',
                'code' => 'PMP201',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Risk Assessment Report', 'weighting' => 70, 'is_must_pass' => false],
                    ['component_name' => 'Mitigation Strategy Plan', 'weighting' => 30, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            
            // HR modules
            [
                'title' => 'Employment Law',
                'code' => 'LAW201',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Legal Case Study Analysis', 'weighting' => 100, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'bi_annually'
            ],
            [
                'title' => 'Recruitment and Selection',
                'code' => 'HRM301',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Recruitment Strategy Design', 'weighting' => 60, 'is_must_pass' => false],
                    ['component_name' => 'Interview Simulation Assessment', 'weighting' => 40, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Performance Management',
                'code' => 'HRM401',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Performance System Design', 'weighting' => 50, 'is_must_pass' => false],
                    ['component_name' => 'Performance Review Roleplay', 'weighting' => 50, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            
            // Software Development modules
            [
                'title' => 'Web Development Fundamentals',
                'code' => 'WEB101',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Website Development Project', 'weighting' => 70, 'is_must_pass' => false],
                    ['component_name' => 'Technical Documentation', 'weighting' => 30, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'monthly'
            ],
            [
                'title' => 'Programming Principles',
                'code' => 'PRG201',
                'credits' => 15,
                'assessment' => [
                    ['component_name' => 'Programming Portfolio', 'weighting' => 50, 'is_must_pass' => false],
                    ['component_name' => 'Algorithm Design Project', 'weighting' => 30, 'is_must_pass' => false],
                    ['component_name' => 'Practical Programming Exam', 'weighting' => 20, 'is_must_pass' => true, 'component_pass_mark' => 50]
                ],
                'standalone' => false,
                'cadence' => 'quarterly'
            ],
            
            // Cybersecurity modules
            [
                'title' => 'Network Security',
                'code' => 'SEC301',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Security Assessment Report', 'weighting' => 60, 'is_must_pass' => false],
                    ['component_name' => 'Penetration Testing Exercise', 'weighting' => 40, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Incident Response',
                'code' => 'SEC401',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Incident Response Plan', 'weighting' => 100, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            
            // Financial Services modules
            [
                'title' => 'Investment Analysis',
                'code' => 'INV301',
                'credits' => 10,
                'assessment' => [
                    ['component_name' => 'Investment Portfolio Project', 'weighting' => 60, 'is_must_pass' => false],
                    ['component_name' => 'Market Analysis Report', 'weighting' => 40, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'quarterly'
            ],
            [
                'title' => 'Banking Operations',
                'code' => 'BNK201',
                'credits' => 5,
                'assessment' => [
                    ['component_name' => 'Banking Process Analysis', 'weighting' => 100, 'is_must_pass' => false]
                ],
                'standalone' => true,
                'cadence' => 'bi_annually'
            ]
        ];
        
        \$modules_created = 0;
        \$instances_created = 0;
        \$target_modules = min($MODULES, count(\$module_templates));
        \$teachers = App\Models\User::where('role', 'teacher')->get();
        
        if (\$teachers->isEmpty()) {
            echo 'No teachers found - creating module instances without tutors' . PHP_EOL;
        }
        
        for (\$i = 0; \$i < \$target_modules; \$i++) {
            \$template = \$module_templates[\$i];
            
            try {
                \$module = App\Models\Module::create([
                    'title' => \$template['title'],
                    'module_code' => \$template['code'],
                    'credit_value' => \$template['credits'],
                    'assessment_strategy' => \$template['assessment'],
                    'allows_standalone_enrolment' => \$template['standalone'],
                    'async_instance_cadence' => \$template['cadence']
                ]);
                
                \$modules_created++;
                
                // Create multiple instances for this module
                \$instances_per_module = max(1, intval($MODULE_INSTANCES / $MODULES));
                
                for (\$j = 0; \$j < \$instances_per_module; \$j++) {
                    \$year = rand(2024, 2026);
                    \$month = rand(1, 12);
                    \$start_date = Carbon\Carbon::create(\$year, \$month, 1);
                    \$end_date = \$start_date->copy()->addMonths(rand(2, 6));
                    
                    \$delivery_style = rand(1, 100) <= 70 ? 'sync' : 'async';
                    \$tutor = \$teachers->isNotEmpty() ? \$teachers->random() : null;
                    
                    \$instance = App\Models\ModuleInstance::create([
                        'module_id' => \$module->id,
                        'tutor_id' => \$tutor ? \$tutor->id : null,
                        'start_date' => \$start_date,
                        'target_end_date' => \$end_date,
                        'delivery_style' => \$delivery_style
                    ]);
                    
                    \$instances_created++;
                }
                
            } catch (Exception \$e) {
                echo 'Error creating module: ' . \$e->getMessage() . PHP_EOL;
            }
        }
        
        echo 'Modules created: ' . \$modules_created . PHP_EOL;
        echo 'Module instances created: ' . \$instances_created . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Modules and instances generated successfully"
}

# Function to create realistic curriculum links
create_curriculum_links() {
    print_status "$BLUE" "=== Creating Curriculum Links ==="
    
    php artisan tinker --execute="
        \$programmeInstances = App\Models\ProgrammeInstance::with('programme')->get();
        \$moduleInstances = App\Models\ModuleInstance::with('module')->get();
        \$links_created = 0;
        
        foreach (\$programmeInstances as \$programmeInstance) {
            \$programme = \$programmeInstance->programme;
            \$nfq_level = \$programme->nfq_level;
            
            // Determine appropriate modules based on programme type and level
            \$suitable_modules = [];
            
            if (stripos(\$programme->title, 'Business') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Marketing') !== false ||
                           stripos(\$mi->module->title, 'Financial') !== false ||
                           stripos(\$mi->module->title, 'Strategic') !== false ||
                           stripos(\$mi->module->title, 'Operations') !== false;
                });
            } elseif (stripos(\$programme->title, 'Digital Marketing') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Social Media') !== false ||
                           stripos(\$mi->module->title, 'SEO') !== false ||
                           stripos(\$mi->module->title, 'Content Marketing') !== false ||
                           stripos(\$mi->module->title, 'Marketing') !== false;
                });
            } elseif (stripos(\$programme->title, 'Data Analytics') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Statistical') !== false ||
                           stripos(\$mi->module->title, 'Machine Learning') !== false ||
                           stripos(\$mi->module->title, 'Database') !== false;
                });
            } elseif (stripos(\$programme->title, 'Project Management') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Project') !== false ||
                           stripos(\$mi->module->title, 'Risk') !== false;
                });
            } elseif (stripos(\$programme->title, 'Human Resource') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Employment') !== false ||
                           stripos(\$mi->module->title, 'Recruitment') !== false ||
                           stripos(\$mi->module->title, 'Performance') !== false;
                });
            } elseif (stripos(\$programme->title, 'Software Development') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Web Development') !== false ||
                           stripos(\$mi->module->title, 'Programming') !== false ||
                           stripos(\$mi->module->title, 'Database') !== false;
                });
            } elseif (stripos(\$programme->title, 'Cyber Security') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Network Security') !== false ||
                           stripos(\$mi->module->title, 'Incident Response') !== false;
                });
            } elseif (stripos(\$programme->title, 'Financial Services') !== false) {
                \$suitable_modules = \$moduleInstances->filter(function(\$mi) {
                    return stripos(\$mi->module->title, 'Investment') !== false ||
                           stripos(\$mi->module->title, 'Banking') !== false ||
                           stripos(\$mi->module->title, 'Financial') !== false;
                });
            }
            
            // Select appropriate number of modules based on programme level
            \$modules_to_link = [];
            if (\$nfq_level <= 6) {
                // Certificate/Diploma - 2-4 modules
                \$modules_to_link = \$suitable_modules->take(rand(2, 4));
            } elseif (\$nfq_level <= 7) {
                // Higher Diploma - 3-6 modules
                \$modules_to_link = \$suitable_modules->take(rand(3, 6));
            } else {
                // Degree/Masters - 4-8 modules
                \$modules_to_link = \$suitable_modules->take(rand(4, 8));
            }
            
            foreach (\$modules_to_link as \$moduleInstance) {
                try {
                    \$programmeInstance->moduleInstances()->attach(\$moduleInstance->id);
                    \$links_created++;
                } catch (Exception \$e) {
                    // Link might already exist, continue
                }
            }
        }
        
        echo 'Curriculum links created: ' . \$links_created . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Curriculum links created successfully"
}

# Function to generate realistic enrolments
generate_enrolments() {
    print_status "$BLUE" "=== Generating Enrolments ($ENROLMENTS) ==="
    
    php artisan tinker --execute="
        \$students = App\Models\Student::where('status', 'active')->get();
        \$programmeInstances = App\Models\ProgrammeInstance::with('moduleInstances')->get();
        \$standaloneModules = App\Models\ModuleInstance::whereHas('module', function(\$query) {
            \$query->where('allows_standalone_enrolment', true);
        })->get();
        \$enrolmentService = app(App\Services\EnrolmentService::class);
        
        \$programme_enrolments = 0;
        \$module_enrolments = 0;
        \$target_enrolments = $ENROLMENTS;
        
        if (\$students->isEmpty()) {
            echo 'No active students found for enrolment' . PHP_EOL;
            exit;
        }
        
        if (\$programmeInstances->isEmpty()) {
            echo 'No programme instances found for enrolment' . PHP_EOL;
            exit;
        }
        
        // 80% programme enrolments, 20% standalone module enrolments
        \$programme_target = intval(\$target_enrolments * 0.8);
        \$module_target = \$target_enrolments - \$programme_target;
        
        // Create programme enrolments
        for (\$i = 0; \$i < \$programme_target && \$i < \$students->count(); \$i++) {
            \$student = \$students[\$i % \$students->count()];
            \$programmeInstance = \$programmeInstances->random();
            
            // Check if student is already enrolled in this programme
            \$existingEnrolment = App\Models\Enrolment::where('student_id', \$student->id)
                ->where('programme_instance_id', \$programmeInstance->id)
                ->first();
            
            if (!\$existingEnrolment) {
                try {
                    \$enrolment = \$enrolmentService->enrolStudentInProgramme(\$student, \$programmeInstance, [
                        'enrolment_date' => Carbon\Carbon::now()->subDays(rand(0, 365))
                    ]);
                    
                    if (\$enrolment) {
                        \$programme_enrolments++;
                    }
                } catch (Exception \$e) {
                    // Continue with next student
                }
            }
            
            if (\$programme_enrolments % 20 === 0) {
                echo 'Programme enrolments: ' . \$programme_enrolments . PHP_EOL;
            }
        }
        
        // Create standalone module enrolments
        if (\$standaloneModules->isNotEmpty()) {
            for (\$i = 0; \$i < \$module_target; \$i++) {
                \$student = \$students->random();
                \$moduleInstance = \$standaloneModules->random();
                
                // Check if student is already enrolled in this module
                \$existingEnrolment = App\Models\Enrolment::where('student_id', \$student->id)
                    ->where('module_instance_id', \$moduleInstance->id)
                    ->first();
                
                if (!\$existingEnrolment) {
                    try {
                        \$enrolment = \$enrolmentService->enrolStudentInModule(\$student, \$moduleInstance, [
                            'enrolment_date' => Carbon\Carbon::now()->subDays(rand(0, 365))
                        ]);
                        
                        if (\$enrolment) {
                            \$module_enrolments++;
                        }
                    } catch (Exception \$e) {
                        // Continue with next attempt
                    }
                }
            }
        }
        
        echo 'Programme enrolments created: ' . \$programme_enrolments . PHP_EOL;
        echo 'Module enrolments created: ' . \$module_enrolments . PHP_EOL;
        echo 'Total enrolments: ' . (\$programme_enrolments + \$module_enrolments) . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Enrolments generated successfully"
}

# Function to generate realistic grade records
generate_grade_records() {
    print_status "$BLUE" "=== Generating Grade Records ==="
    
    php artisan tinker --execute="
        \$gradeRecords = App\Models\StudentGradeRecord::with(['student', 'moduleInstance.module'])->get();
        \$teachers = App\Models\User::where('role', 'teacher')->get();
        \$graded_count = 0;
        \$total_records = \$gradeRecords->count();
        
        if (\$teachers->isEmpty()) {
            echo 'No teachers found for grading' . PHP_EOL;
            exit;
        }
        
        foreach (\$gradeRecords as \$index => \$record) {
            // Skip if already graded
            if (\$record->grade !== null) {
                continue;
            }
            
            \$student = \$record->student;
            \$teacher = \$teachers->random();
            
            // Determine student performance pattern based on their characteristics
            \$age = \$student->date_of_birth ? Carbon\Carbon::now()->diffInYears(\$student->date_of_birth) : 25;
            \$performance_pattern = 'average'; // Default
            
            if (\$age >= 35) {
                // Mature students tend to be more motivated
                \$performance_pattern = rand(1, 100) <= 70 ? 'professional' : 'average';
            } elseif (\$age <= 22) {
                // Traditional students have mixed performance
                \$rand = rand(1, 100);
                if (\$rand <= 20) {
                    \$performance_pattern = 'high_achiever';
                } elseif (\$rand <= 70) {
                    \$performance_pattern = 'average';
                } else {
                    \$performance_pattern = 'struggling';
                }
            } else {
                // Working age students
                \$rand = rand(1, 100);
                if (\$rand <= 15) {
                    \$performance_pattern = 'high_achiever';
                } elseif (\$rand <= 25) {
                    \$performance_pattern = 'professional';
                } elseif (\$rand <= 75) {
                    \$performance_pattern = 'average';
                } else {
                    \$performance_pattern = 'struggling';
                }
            }
            
            // Generate grade based on pattern
            \$grade = 50; // Default passing grade
            \$assessment_type = strtolower(\$record->assessment_component_name);
            
            switch (\$performance_pattern) {
                case 'high_achiever':
                    \$grade = rand(75, 95) + (rand(0, 100) / 100);
                    break;
                case 'professional':
                    if (strpos(\$assessment_type, 'project') !== false || 
                        strpos(\$assessment_type, 'report') !== false ||
                        strpos(\$assessment_type, 'portfolio') !== false) {
                        \$grade = rand(70, 85) + (rand(0, 100) / 100);
                    } else {
                        \$grade = rand(60, 75) + (rand(0, 100) / 100);
                    }
                    break;
                case 'average':
                    \$grade = rand(55, 75) + (rand(0, 100) / 100);
                    break;
                case 'struggling':
                    \$grade = rand(30, 55) + (rand(0, 100) / 100);
                    break;
            }
            
            // Generate realistic feedback
            \$feedback_templates = [
                'high_achiever' => [
                    'Excellent work demonstrating deep understanding.',
                    'Outstanding analysis with clear critical thinking.',
                    'Exceptional quality with innovative approaches.',
                ],
                'professional' => [
                    'Good practical application of concepts.',
                    'Strong real-world examples enhance the work.',
                    'Professional insights add significant value.',
                ],
                'average' => [
                    'Good work that meets the learning objectives.',
                    'Solid understanding with room for improvement.',
                    'Satisfactory completion of requirements.',
                ],
                'struggling' => [
                    'Basic understanding demonstrated, needs development.',
                    'Some good points but analysis could be deeper.',
                    'Shows effort but would benefit from support.',
                ]
            ];
            
            \$feedback_options = \$feedback_templates[\$performance_pattern];
            \$feedback = \$feedback_options[array_rand(\$feedback_options)];
            
            // 70% of assessments should be graded and visible
            \$should_grade = rand(1, 100) <= 70;
            \$should_be_visible = rand(1, 100) <= 80;
            
            if (\$should_grade) {
                try {
                    \$record->update([
                        'grade' => \$grade,
                        'max_grade' => 100,
                        'feedback' => \$feedback,
                        'submission_date' => Carbon\Carbon::now()->subDays(rand(1, 30)),
                        'graded_date' => Carbon\Carbon::now()->subDays(rand(0, 15)),
                        'graded_by_staff_id' => \$teacher->id,
                        'is_visible_to_student' => \$should_be_visible,
                        'release_date' => \$should_be_visible ? Carbon\Carbon::now()->subDays(rand(0, 10)) : null,
                    ]);
                    
                    \$graded_count++;
                } catch (Exception \$e) {
                    echo 'Error grading record: ' . \$e->getMessage() . PHP_EOL;
                }
            }
            
            if (\$graded_count % 50 === 0) {
                echo 'Graded records: ' . \$graded_count . ' / ' . \$total_records . PHP_EOL;
            }
        }
        
        echo 'Total grade records processed: ' . \$graded_count . PHP_EOL;
    " 2>/dev/null
    
    print_status "$GREEN" "✅ Grade records generated successfully"
}

# Function to create problematic data for testing edge cases
create_problematic_data() {
    if [ "$INCLUDE_PROBLEMATIC" = "true" ]; then
        print_status "$BLUE" "=== Creating Problematic Data for Edge Case Testing ==="
        
        php artisan tinker --execute="
            try {
                // Create students with various issues for testing
                
                // 1. Student with very long name
                \$longNameStudent = App\Models\Student::create([
                    'student_number' => App\Models\Student::generateStudentNumber(),
                    'first_name' => 'Extraordinarily-Long-First-Name-That-Tests-Field-Limits',
                    'last_name' => 'Extremely-Long-Surname-That-Also-Tests-Database-Field-Length-Constraints',
                    'email' => 'very.long.name.test@student.ie',
                    'phone' => '0851234567',
                    'status' => 'active'
                ]);
                
                // 2. Student with special characters
                \$specialCharStudent = App\Models\Student::create([
                    'student_number' => App\Models\Student::generateStudentNumber(),
                    'first_name' => 'Seán-Pádraig',
                    'last_name' => 'O\'Súilleabháin-Mac Cárthaigh',
                    'email' => 'sean.special@student.ie',
                    'phone' => '0851234568',
                    'status' => 'active'
                ]);
                
                // 3. Create student with future birth date (should be invalid)
                try {
                    \$futureBirthStudent = App\Models\Student::create([
                        'student_number' => App\Models\Student::generateStudentNumber(),
                        'first_name' => 'Future',
                        'last_name' => 'Born',
                        'email' => 'future.born@student.ie',
                        'phone' => '0851234569',
                        'date_of_birth' => Carbon\Carbon::now()->addYears(1),
                        'status' => 'active'
                    ]);
                    echo 'WARNING: Future birth date was allowed' . PHP_EOL;
                } catch (Exception \$e) {
                    echo 'GOOD: Future birth date was rejected' . PHP_EOL;
                }
                
                // 4. Create very old student (100+ years)
                \$oldStudent = App\Models\Student::create([
                    'student_number' => App\Models\Student::generateStudentNumber(),
                    'first_name' => 'Very',
                    'last_name' => 'Old',
                    'email' => 'very.old@student.ie',
                    'phone' => '0851234570',
                    'date_of_birth' => Carbon\Carbon::create(1920, 1, 1),
                    'status' => 'active'
                ]);
                
                // 5. Create students with edge case statuses
                \$enquiryStudent = App\Models\Student::create([
                    'student_number' => App\Models\Student::generateStudentNumber(),
                    'first_name' => 'Long',
                    'last_name' => 'Enquiry',
                    'email' => 'long.enquiry@student.ie',
                    'phone' => '0851234571',
                    'status' => 'enquiry'
                ]);
                
                \$withdrawnStudent = App\Models\Student::create([
                    'student_number' => App\Models\Student::generateStudentNumber(),
                    'first_name' => 'Withdrawn',
                    'last_name' => 'Student',
                    'email' => 'withdrawn.student@student.ie',
                    'phone' => '0851234572',
                    'status' => 'withdrawn'
                ]);
                
                echo 'Problematic test data created successfully' . PHP_EOL;
                
            } catch (Exception \$e) {
                echo 'Error creating problematic data: ' . \$e->getMessage() . PHP_EOL;
            }
        " 2>/dev/null
        
        print_status "$GREEN" "✅ Problematic data created for edge case testing"
    fi
}

# Function to generate data summary report
generate_summary_report() {
    print_status "$BLUE" "=== Data Generation Summary ==="
    
    php artisan tinker --execute="
        // Count all generated data
        \$students = App\Models\Student::count();
        \$users = App\Models\User::count();
        \$programmes = App\Models\Programme::count();
        \$programme_instances = App\Models\ProgrammeInstance::count();
        \$modules = App\Models\Module::count();
        \$module_instances = App\Models\ModuleInstance::count();
        \$enrolments = App\Models\Enrolment::count();
        \$grade_records = App\Models\StudentGradeRecord::count();
        \$graded_records = App\Models\StudentGradeRecord::whereNotNull('grade')->count();
        \$curriculum_links = App\Models\ProgrammeInstance::withCount('moduleInstances')->get()->sum('module_instances_count');
        
        echo '📊 DATA GENERATION SUMMARY' . PHP_EOL;
        echo '=========================' . PHP_EOL;
        echo 'Students: ' . \$students . PHP_EOL;
        echo 'Users (Staff): ' . \$users . PHP_EOL;
        echo 'Programmes: ' . \$programmes . PHP_EOL;
        echo 'Programme Instances: ' . \$programme_instances . PHP_EOL;
        echo 'Modules: ' . \$modules . PHP_EOL;
        echo 'Module Instances: ' . \$module_instances . PHP_EOL;
        echo 'Enrolments: ' . \$enrolments . PHP_EOL;
        echo 'Grade Records: ' . \$grade_records . PHP_EOL;
        echo 'Graded Records: ' . \$graded_records . PHP_EOL;
        echo 'Curriculum Links: ' . \$curriculum_links . PHP_EOL;
        echo PHP_EOL;
        
        // Performance insights
        \$programme_enrolments = App\Models\Enrolment::where('enrolment_type', 'programme')->count();
        \$module_enrolments = App\Models\Enrolment::where('enrolment_type', 'module')->count();
        \$active_students = App\Models\Student::where('status', 'active')->count();
        \$visible_grades = App\Models\StudentGradeRecord::where('is_visible_to_student', true)->count();
        
        echo '📈 DATA INSIGHTS' . PHP_EOL;
        echo '===============' . PHP_EOL;
        echo 'Programme Enrolments: ' . \$programme_enrolments . PHP_EOL;
        echo 'Standalone Module Enrolments: ' . \$module_enrolments . PHP_EOL;
        echo 'Active Students: ' . \$active_students . PHP_EOL;
        echo 'Visible Grade Records: ' . \$visible_grades . ' / ' . \$graded_records . PHP_EOL;
        echo 'Grade Visibility Rate: ' . round((\$visible_grades / max(\$graded_records, 1)) * 100, 1) . '%' . PHP_EOL;
        echo PHP_EOL;
        
        // Student distribution
        \$student_statuses = App\Models\Student::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        echo '👥 STUDENT STATUS DISTRIBUTION' . PHP_EOL;
        echo '=============================' . PHP_EOL;
        foreach (\$student_statuses as \$status) {
            echo \$status->status . ': ' . \$status->count . PHP_EOL;
        }
        echo PHP_EOL;
        
        // Module delivery styles
        \$delivery_styles = App\Models\ModuleInstance::select('delivery_style', DB::raw('count(*) as count'))
            ->groupBy('delivery_style')
            ->get();
        
        echo '🏫 MODULE DELIVERY STYLES' . PHP_EOL;
        echo '========================' . PHP_EOL;
        foreach (\$delivery_styles as \$style) {
            echo \$style->delivery_style . ': ' . \$style->count . PHP_EOL;
        }
        
    " 2>/dev/null
    
    echo ""
    print_status "$GREEN" "🎉 Test data generation completed successfully!"
    echo ""
    print_status "$BLUE" "🚀 READY FOR WORKFLOW TESTING"
    echo "You can now run:"
    echo "  • ./scripts/workflow-validation.sh     (Validate system integrity)"
    echo "  • ./scripts/workflow-automation.sh     (Run automated workflow tests)"
    echo "  • php artisan analytics:compute        (Generate analytics data)"
    echo ""
}

# Main execution function
main() {
    echo "Starting comprehensive test data generation..."
    echo "Configuration:"
    echo "  Data Size: $DATA_SIZE"
    echo "  Include Historical: $INCLUDE_HISTORICAL"
    echo "  Include Problematic: $INCLUDE_PROBLEMATIC"
    echo "  Reset Database: $RESET_DATABASE"
    echo ""
    
    # Check Laravel application
    if ! php artisan --version >/dev/null 2>&1; then
        print_status "$RED" "❌ Laravel application not available"
        exit 1
    fi
    
    # Get data volumes for selected size
    get_data_volumes
    echo ""
    
    # Reset database if requested
    reset_database
    
    # Generate all test data
    generate_staff_users
    echo ""
    generate_programmes
    echo ""
    generate_modules
    echo ""
    create_curriculum_links
    echo ""
    generate_students
    echo ""
    generate_enrolments
    echo ""
    generate_grade_records
    echo ""
    create_problematic_data
    echo ""
    
    # Generate final summary
    generate_summary_report
}

# Handle script arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --help, -h              Show this help message"
        echo "  --size SIZE             Data size: small, medium, large, bulk (default: medium)"
        echo "  --reset                 Reset database before generating data"
        echo "  --no-historical         Skip historical data generation"
        echo "  --no-problematic        Skip problematic data for edge cases"
        echo "  --students-only         Generate only students and basic data"
        echo "  --programmes-only       Generate only programmes and modules"
        echo ""
        echo "Environment Variables:"
        echo "  DATA_SIZE              Data size: small, medium, large, bulk"
        echo "  INCLUDE_HISTORICAL     Include historical data: true/false"
        echo "  INCLUDE_PROBLEMATIC    Include edge case data: true/false"
        echo "  RESET_DATABASE         Reset database: true/false"
        exit 0
        ;;
    --size)
        DATA_SIZE="$2"
        shift 2
        main
        ;;
    --reset)
        RESET_DATABASE=true
        main
        ;;
    --no-historical)
        INCLUDE_HISTORICAL=false
        main
        ;;
    --no-problematic)
        INCLUDE_PROBLEMATIC=false
        main
        ;;
    --students-only)
        echo "Generating students and users only..."
        get_data_volumes
        generate_staff_users
        generate_students
        generate_summary_report
        ;;
    --programmes-only)
        echo "Generating programmes and modules only..."
        get_data_volumes
        generate_staff_users
        generate_programmes
        generate_modules
        create_curriculum_links
        generate_summary_report
        ;;
    *)
        main
        ;;
esac