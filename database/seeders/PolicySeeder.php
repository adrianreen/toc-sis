<?php

namespace Database\Seeders;

use App\Models\Policy;
use App\Models\PolicyAssignment;
use App\Models\PolicyCategory;
use App\Models\Programme;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        // First ensure categories exist
        $this->createPolicyCategories();

        // Then create policies
        $this->createPolicies();

        $this->command->info('Policies and categories created successfully!');
    }

    private function createPolicyCategories()
    {
        $categories = [
            [
                'name' => 'Academic Policies',
                'description' => 'Policies related to academic standards, assessment, and student progression',
            ],
            [
                'name' => 'Student Conduct',
                'description' => 'Policies governing student behavior, ethics, and disciplinary procedures',
            ],
            [
                'name' => 'Health & Safety',
                'description' => 'Policies ensuring the health, safety, and welfare of students and staff',
            ],
            [
                'name' => 'Data Protection',
                'description' => 'Policies related to GDPR compliance and student data protection',
            ],
            [
                'name' => 'IT & Digital',
                'description' => 'Policies governing the use of IT systems, software, and digital resources',
            ],
            [
                'name' => 'Quality Assurance',
                'description' => 'Policies ensuring educational quality and continuous improvement',
            ],
        ];

        foreach ($categories as $category) {
            PolicyCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }

    private function createPolicies()
    {
        $academicCategory = PolicyCategory::where('name', 'Academic Policies')->first();
        $conductCategory = PolicyCategory::where('name', 'Student Conduct')->first();
        $healthSafetyCategory = PolicyCategory::where('name', 'Health & Safety')->first();
        $dataProtectionCategory = PolicyCategory::where('name', 'Data Protection')->first();
        $itDigitalCategory = PolicyCategory::where('name', 'IT & Digital')->first();
        $qualityCategory = PolicyCategory::where('name', 'Quality Assurance')->first();

        $policies = [
            // Academic Policies
            [
                'title' => 'Assessment and Grading Policy',
                'description' => 'Guidelines for fair and consistent assessment practices across all programmes',
                'content' => $this->getAssessmentPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $academicCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],
            [
                'title' => 'Academic Integrity Policy',
                'description' => 'Policy outlining expectations for academic honesty and consequences for misconduct',
                'content' => $this->getAcademicIntegrityPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $academicCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],
            [
                'title' => 'Extensions and Deferrals Policy',
                'description' => 'Procedures for requesting and granting extensions or deferrals for assessments',
                'content' => $this->getExtensionsPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $academicCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],

            // Student Conduct
            [
                'title' => 'Student Code of Conduct',
                'description' => 'Expected standards of behavior for all students enrolled at The Open College',
                'content' => $this->getStudentConductPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $conductCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],
            [
                'title' => 'Anti-Bullying and Harassment Policy',
                'description' => 'Zero-tolerance policy on bullying, harassment, and discrimination',
                'content' => $this->getAntiBullyingPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $conductCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],

            // Health & Safety
            [
                'title' => 'Campus Safety and Security Policy',
                'description' => 'Guidelines for maintaining a safe and secure learning environment',
                'content' => $this->getCampusSafetyPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $healthSafetyCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],
            [
                'title' => 'Emergency Procedures Policy',
                'description' => 'Procedures to follow during various emergency situations',
                'content' => $this->getEmergencyProceduresPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $healthSafetyCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],

            // Data Protection
            [
                'title' => 'Student Data Protection Policy',
                'description' => 'How The Open College collects, uses, and protects student personal data',
                'content' => $this->getDataProtectionPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $dataProtectionCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],

            // IT & Digital
            [
                'title' => 'Acceptable Use of IT Resources Policy',
                'description' => 'Guidelines for appropriate use of college IT systems and resources',
                'content' => $this->getITUsagePolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $itDigitalCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],
            [
                'title' => 'Online Learning Platform Policy',
                'description' => 'Guidelines for using Moodle and other online learning platforms',
                'content' => $this->getOnlineLearningPolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $itDigitalCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],

            // Quality Assurance
            [
                'title' => 'Academic Quality Assurance Policy',
                'description' => 'Framework for maintaining and improving academic standards',
                'content' => $this->getQualityAssurancePolicyContent(),
                'scope' => 'college',
                'status' => 'published',
                'category_id' => $qualityCategory->id,
                'published_at' => now(),
                'created_by' => 1,
            ],
        ];

        foreach ($policies as $policyData) {
            $policy = Policy::create($policyData);

            // Create some programme-specific assignments for certain policies
            if (in_array($policy->title, ['Assessment and Grading Policy', 'Academic Integrity Policy'])) {
                $this->assignPolicyToProgrammes($policy);
            }
        }
    }

    private function assignPolicyToProgrammes(Policy $policy)
    {
        $programmes = Programme::take(3)->get(); // Assign to first 3 programmes as example

        foreach ($programmes as $programme) {
            PolicyAssignment::create([
                'policy_id' => $policy->id,
                'programme_id' => $programme->id,
                'module_id' => null,
            ]);
        }
    }

    private function getAssessmentPolicyContent(): string
    {
        return <<<'EOD'
# Assessment and Grading Policy

## Purpose
This policy ensures fair, consistent, and transparent assessment practices across all programmes at The Open College.

## Assessment Principles
1. **Validity**: Assessments measure what they are intended to measure
2. **Reliability**: Consistent results across different assessors and time periods
3. **Fairness**: Equal opportunity for all students to demonstrate their learning
4. **Transparency**: Clear criteria and expectations communicated to students

## Grading Scale
- **85-100%**: Distinction (Grade A)
- **70-84%**: Merit (Grade B)
- **60-69%**: Pass (Grade C)
- **40-59%**: Marginal Pass (Grade D)
- **0-39%**: Fail (Grade F)

## Assessment Types
### Formative Assessment
- Ongoing feedback to support learning
- Does not contribute to final grade
- Regular quizzes, discussions, and practice exercises

### Summative Assessment
- Evaluates student learning at the end of an instructional unit
- Contributes to final grade
- Examinations, major projects, portfolios

## Feedback Timeline
- Grades and feedback must be provided within 15 working days of submission
- For major assessments, feedback should include:
  - Overall grade and component breakdown
  - Strengths demonstrated
  - Areas for improvement
  - Specific recommendations for future learning

## Grade Appeals Process
Students may appeal grades following these steps:
1. Informal discussion with the module tutor
2. Formal written appeal to the Academic Manager
3. External review if required

For full details, contact Student Services.
EOD;
    }

    private function getAcademicIntegrityPolicyContent(): string
    {
        return <<<'EOD'
# Academic Integrity Policy

## Purpose
The Open College is committed to maintaining the highest standards of academic integrity. This policy outlines expectations and consequences regarding academic honesty.

## Definition of Academic Misconduct
Academic misconduct includes but is not limited to:

### Plagiarism
- Using someone else's work without proper attribution
- Self-plagiarism (resubmitting your own previous work)
- Inadequate paraphrasing or citation

### Cheating
- Unauthorized collaboration on individual assessments
- Using unauthorized materials during examinations
- Copying from other students

### Fabrication
- Making up data, sources, or citations
- Falsifying research results

## Prevention Strategies
- All students must complete academic integrity training
- Proper citation training provided in all programmes
- Use of plagiarism detection software
- Clear assessment instructions and expectations

## Consequences
### First Offense (Minor)
- Warning and educational intervention
- Resubmission required with grade penalty

### First Offense (Major) or Second Offense
- Failure of the assessment
- Mandatory academic integrity workshop
- Note on academic record

### Repeated or Severe Violations
- Failure of the module
- Possible suspension or expulsion
- Permanent note on academic record

## Support Resources
- Academic Skills Support Centre
- Library citation workshops
- Student Services counseling
- Peer mentoring programmes

Students are encouraged to seek help before submitting work they are unsure about.
EOD;
    }

    private function getExtensionsPolicyContent(): string
    {
        return <<<'EOD'
# Extensions and Deferrals Policy

## Purpose
This policy provides clear guidelines for requesting extensions and deferrals for assessments and modules.

## Assessment Extensions
### Eligibility Criteria
Extensions may be granted for:
- Serious illness (medical certificate required)
- Family bereavement
- Significant personal circumstances
- Technical difficulties beyond student control

### Application Process
1. Submit request at least 48 hours before deadline (except emergencies)
2. Complete extension request form
3. Provide supporting documentation
4. Await approval from module tutor or Academic Manager

### Duration
- Standard extension: up to 7 days
- Extended circumstances: up to 14 days
- Exceptional cases: to be reviewed individually

## Module Deferrals
### Eligibility
Deferrals may be granted for:
- Serious health issues requiring extended absence
- Significant family circumstances
- Work-related commitments (part-time students)
- Financial hardship

### Application Process
1. Meet with Student Services advisor
2. Complete deferral application form
3. Provide supporting documentation
4. Academic Manager approval required

### Conditions
- Must be requested before 50% module completion
- May defer for up to one academic year
- Must re-enroll for deferred modules
- Original grades retained for completed assessments

## Appeals Process
If your request is denied, you may appeal by:
1. Submitting written appeal with additional evidence
2. Meeting with Academic Appeals Committee
3. External review if necessary

Contact Student Services for guidance on the appeals process.
EOD;
    }

    private function getStudentConductPolicyContent(): string
    {
        return <<<'EOD'
# Student Code of Conduct

## Our Community Values
The Open College fosters a respectful, inclusive, and supportive learning environment based on:
- **Respect**: Treating all community members with dignity
- **Integrity**: Honesty in all academic and personal interactions
- **Responsibility**: Accountability for one's actions and commitments
- **Excellence**: Striving for high standards in all endeavors

## Expected Behaviors
### Academic Environment
- Attend classes regularly and punctually
- Participate constructively in discussions
- Complete assignments honestly and on time
- Respect intellectual property and academic integrity

### Social Interactions
- Treat all students, staff, and visitors with respect
- Use appropriate language in all communications
- Respect diverse backgrounds, beliefs, and perspectives
- Resolve conflicts through constructive dialogue

### Campus and Online Spaces
- Follow health and safety guidelines
- Respect college property and facilities
- Maintain professional standards in online interactions
- Report safety concerns promptly

## Prohibited Behaviors
- Harassment, discrimination, or bullying
- Academic dishonesty or cheating
- Disruptive behavior in classes or college events
- Inappropriate use of IT resources
- Violation of health and safety protocols

## Consequences
Violations may result in:
- Verbal or written warnings
- Educational interventions
- Restriction from college facilities
- Suspension or expulsion
- Referral to external authorities if applicable

## Support and Resources
- Student Services counseling
- Conflict resolution services
- Mental health support
- Academic skills development
- Peer mentoring programs

We encourage students to seek help when facing challenges rather than resorting to inappropriate behavior.
EOD;
    }

    private function getAntiBullyingPolicyContent(): string
    {
        return <<<'EOD'
# Anti-Bullying and Harassment Policy

## Zero Tolerance Statement
The Open College maintains a zero-tolerance policy toward bullying, harassment, and discrimination in any form.

## Definitions
### Bullying
Repeated aggressive behavior intended to hurt, intimidate, or harm another person, including:
- Physical aggression or threats
- Verbal abuse or insults
- Social exclusion or isolation
- Cyberbullying through digital platforms

### Harassment
Unwelcome conduct that creates an intimidating, hostile, or offensive environment, including:
- Sexual harassment
- Discriminatory comments or behavior
- Stalking or persistent unwanted contact
- Abuse of power relationships

### Discrimination
Unfair treatment based on protected characteristics:
- Gender, race, ethnicity, or nationality
- Religion or belief system
- Sexual orientation or gender identity
- Age or disability status
- Economic background

## Reporting Procedures
### Immediate Support
- Contact emergency services if in immediate danger
- Speak with any staff member for immediate assistance
- Use anonymous reporting system if preferred

### Formal Reporting
1. Report to Student Services or Academic Manager
2. Complete incident report form
3. Provide detailed account and any evidence
4. Cooperate with investigation process

### Support During Process
- Counseling and emotional support available
- Academic accommodations if needed
- Protection from retaliation
- Regular updates on investigation progress

## Investigation Process
1. Prompt acknowledgment of report (within 24 hours)
2. Thorough and impartial investigation
3. Interview all relevant parties
4. Review available evidence
5. Determination and appropriate action within 10 working days

## Consequences
Proven violations may result in:
- Mandatory counseling or education programs
- Formal warnings and behavior contracts
- Restrictions on college activities
- Suspension or expulsion
- Referral to external authorities

## Prevention Initiatives
- Regular awareness campaigns and training
- Bystander intervention education
- Inclusive community building activities
- Clear reporting mechanisms
- Regular policy review and updates

Remember: Everyone has the right to learn and work in a safe, respectful environment.
EOD;
    }

    private function getCampusSafetyPolicyContent(): string
    {
        return <<<'EOD'
# Campus Safety and Security Policy

## Our Commitment
The Open College is committed to providing a safe and secure environment for all students, staff, and visitors.

## Safety Responsibilities
### College Responsibilities
- Maintain secure facilities and equipment
- Provide safety training and information
- Conduct regular safety assessments
- Respond promptly to safety concerns
- Coordinate with emergency services

### Individual Responsibilities
- Follow all safety procedures and guidelines
- Report safety hazards immediately
- Participate in safety training programs
- Carry student ID cards at all times
- Be aware of emergency procedures

## Access and Security
### Building Access
- Main entrances secured outside operating hours
- Student ID required for after-hours access
- Visitors must sign in and be escorted
- Report suspicious activity immediately

### Personal Security
- Keep personal belongings secure
- Don't share access codes or ID cards
- Travel in groups during late hours
- Park in well-lit, designated areas

## Emergency Procedures
### Fire Emergency
1. Activate nearest fire alarm
2. Evacuate via nearest safe exit
3. Proceed to designated assembly points
4. Don't use elevators
5. Don't re-enter until cleared by authorities

### Medical Emergency
1. Call emergency services (999/112)
2. Notify college security
3. Provide first aid if trained
4. Don't move seriously injured persons
5. Stay with casualty until help arrives

### Security Incident
1. Ensure personal safety first
2. Contact campus security immediately
3. Preserve evidence if safe to do so
4. Cooperate with authorities
5. Seek support services if needed

## Health and Wellness
### General Health Guidelines
- Report health and safety hazards
- Follow hygiene protocols
- Don't attend if seriously unwell
- Inform staff of medical conditions that may affect safety

### Mental Health Support
- Counseling services available
- Crisis intervention protocols
- Referral to external services
- Confidential support options

## Incident Reporting
All incidents must be reported:
- Injuries or near misses
- Security breaches
- Property damage
- Threatening behavior
- Any safety concerns

Contact campus security 24/7 or submit online incident reports.
EOD;
    }

    private function getEmergencyProceduresPolicyContent(): string
    {
        return <<<'EOD'
# Emergency Procedures Policy

## Emergency Contact Information
- **Emergency Services**: 999 or 112
- **Campus Security**: [Insert local number]
- **Student Services**: [Insert number]
- **Facilities Management**: [Insert number]

## Types of Emergencies
### Medical Emergencies
**Signs requiring immediate response:**
- Unconsciousness or altered consciousness
- Severe bleeding or trauma
- Chest pain or difficulty breathing
- Severe allergic reactions
- Mental health crisis

**Response Steps:**
1. Ensure scene safety
2. Call emergency services immediately
3. Provide basic first aid if trained
4. Notify campus security
5. Document incident

### Fire Emergencies
**Prevention:**
- Don't overload electrical outlets
- Report faulty equipment immediately
- Keep exits and fire equipment clear
- Follow no-smoking policies

**Response (RACE Protocol):**
- **Rescue**: Remove anyone in immediate danger
- **Alarm**: Activate fire alarm system
- **Contain**: Close doors to slow fire spread
- **Evacuate**: Use nearest safe exit route

### Severe Weather
**Preparation:**
- Monitor weather alerts
- Know shelter locations
- Keep emergency supplies available
- Plan alternative transportation

**During Event:**
- Move to designated shelter areas
- Stay away from windows
- Follow staff instructions
- Don't leave until all-clear given

### Security Threats
**Prevention:**
- Report suspicious behavior
- Keep personal information private
- Secure belongings and access cards
- Be aware of surroundings

**Response:**
- Remove yourself from danger
- Call emergency services
- Follow lockdown procedures if announced
- Provide information to authorities

## Evacuation Procedures
### Assembly Points
- **Building A**: Front car park
- **Building B**: Sports field
- **Building C**: Side entrance area

### Evacuation Guidelines
- Use nearest safe exit
- Walk quickly but don't run
- Help those who need assistance
- Don't use elevators
- Don't re-enter until authorized

### Special Considerations
- Students with disabilities have personalized evacuation plans
- Visitors should be escorted by college personnel
- International students receive specific guidance
- Remote learning students have separate protocols

## Communication During Emergencies
### Internal Communication
- College alert system (text/email)
- Public address announcements
- Emergency wardens coordination
- Social media updates

### External Communication
- Family notification procedures
- Media relations (management only)
- Emergency services coordination
- Government agency reporting

## Post-Emergency Procedures
### Immediate Actions
- Account for all persons
- Provide medical attention as needed
- Secure the incident scene
- Begin incident documentation

### Follow-up Actions
- Incident investigation and reporting
- Counseling and support services
- Facility damage assessment
- Emergency plan review and updates

Remember: Your safety is our priority. When in doubt, evacuate and call for help.
EOD;
    }

    private function getDataProtectionPolicyContent(): string
    {
        return <<<'EOD'
# Student Data Protection Policy

## Purpose
This policy explains how The Open College collects, uses, stores, and protects student personal data in compliance with GDPR and Irish data protection laws.

## Data We Collect
### Academic Information
- Student records and academic progress
- Assessment results and grades
- Attendance and participation records
- Academic integrity incidents

### Personal Information
- Contact details and emergency contacts
- Identity verification documents
- Financial information for fees
- Health information (where relevant)
- Photographs for ID cards and promotional materials

### Technical Data
- IT system usage logs
- Online learning platform activity
- Email and communication records
- CCTV footage (where applicable)

## How We Use Your Data
### Educational Purposes
- Delivering courses and assessments
- Tracking academic progress
- Providing student support services
- Quality assurance and improvement

### Administrative Purposes
- Managing student records
- Fee collection and financial administration
- Communications about college matters
- Health and safety requirements

### Legal and Regulatory
- Meeting statutory reporting requirements
- Compliance with education regulations
- Preventing and investigating misconduct
- Protecting college interests

## Data Sharing
### Within the College
Data is shared only with staff who need it for legitimate educational or administrative purposes.

### External Sharing (Limited Circumstances)
- **Regulatory Bodies**: As required by law
- **Emergency Services**: For health and safety
- **Partner Organizations**: For placements (with consent)
- **References**: For employment/further study (with consent)

## Your Rights
### Access and Rectification
- Request copies of your personal data
- Correct inaccurate or incomplete information
- Update contact details and preferences

### Data Portability
- Receive your data in electronic format
- Transfer data to another organization

### Erasure and Restriction
- Request deletion of personal data (subject to legal requirements)
- Restrict processing in certain circumstances

### Objection
- Object to processing for marketing purposes
- Object to automated decision-making

## Data Security
### Technical Safeguards
- Encrypted data storage and transmission
- Regular security updates and patches
- Access controls and authentication
- Regular backups and recovery procedures

### Organizational Measures
- Staff training on data protection
- Clear data handling procedures
- Regular security audits
- Incident response procedures

## Data Retention
### Student Records
- Academic records: Retained permanently
- Assessment materials: 7 years after graduation
- Disciplinary records: 7 years after incident
- Financial records: 7 years after completion

### Application and Inquiry Data
- Successful applications: As above
- Unsuccessful applications: 2 years
- General inquiries: 1 year

## Making a Complaint
If you're not satisfied with how we handle your personal data:
1. Contact our Data Protection Officer
2. Submit formal complaint to college management
3. Contact the Data Protection Commission (Ireland)

## Contact Information
**Data Protection Officer**
Email: dataprotection@theopencollege.ie
Phone: [Insert number]
Address: [Insert postal address]

This policy is reviewed annually and updated as necessary.
EOD;
    }

    private function getITUsagePolicyContent(): string
    {
        return <<<'EOD'
# Acceptable Use of IT Resources Policy

## Purpose
This policy ensures responsible and ethical use of The Open College's IT resources while protecting the security and integrity of our systems.

## Scope
This policy applies to all:
- College-owned devices and equipment
- Network and internet access
- Software and applications
- Email and communication systems
- Online learning platforms

## Acceptable Use
### Educational Activities
- Course-related research and assignments
- Accessing learning materials and resources
- Communicating with staff and fellow students
- Participating in online discussions and activities

### Personal Use (Limited)
- Brief personal email and internet browsing
- Must not interfere with educational activities
- Must comply with all policy requirements
- Subject to monitoring and restrictions

## Prohibited Activities
### Illegal or Unethical Conduct
- Downloading or sharing copyrighted material illegally
- Accessing inappropriate or offensive content
- Harassment, bullying, or threatening communications
- Identity theft or impersonation

### Security Violations
- Sharing login credentials or passwords
- Attempting to bypass security measures
- Installing unauthorized software
- Connecting unauthorized devices

### System Abuse
- Excessive personal use affecting performance
- Deliberate introduction of viruses or malware
- Unauthorized access to restricted systems
- Interfering with others' use of systems

## Password Security
### Requirements
- Minimum 8 characters with complexity
- Unique passwords for different systems
- Change passwords regularly
- Never share with others

### Best Practices
- Use password manager tools
- Enable two-factor authentication where available
- Report suspected password breaches immediately
- Log out when leaving computers unattended

## Software and Downloads
### Approved Software
- Only install software approved by IT Services
- Request new software through proper channels
- Keep all software updated
- Use only licensed software

### Prohibited Downloads
- Peer-to-peer file sharing applications
- Unlicensed or cracked software
- Potentially harmful files or programs
- Large personal files that consume resources

## Email and Communications
### Professional Standards
- Use college email for educational purposes
- Maintain professional tone and language
- Include appropriate signatures
- Respect confidentiality

### Prohibited Communications
- Spam, chain letters, or mass personal mailings
- Discriminatory or offensive content
- Commercial activities or advertising
- Communications that could damage college reputation

## Monitoring and Privacy
### System Monitoring
- IT resources may be monitored for security and compliance
- Internet activity logs are maintained
- Email may be reviewed if necessary
- CCTV in computer labs for security

### Privacy Expectations
- Limited privacy expectation on college systems
- Personal data protected according to policy
- Monitoring conducted only for legitimate purposes
- Individual monitoring requires authorization

## Mobile Devices and BYOD
### Personal Devices
- Connect only to guest networks unless authorized
- Install required security software
- Report lost or stolen devices immediately
- Don't store college data on personal devices

### College-Issued Devices
- Primary use for educational purposes
- Follow all security requirements
- Report damage or theft immediately
- Return upon request or program completion

## Consequences of Violations
### Progressive Discipline
- **Warning**: First minor violation
- **Restricted Access**: Repeated or moderate violations
- **Suspension**: Serious violations
- **Permanent Ban**: Severe or repeated serious violations

### Additional Consequences
- Academic disciplinary action
- Referral to external authorities
- Financial liability for damages
- Legal action where appropriate

## Reporting Issues
Report the following immediately:
- Suspected security breaches
- Inappropriate content or behavior
- System problems or malfunctions
- Lost or stolen equipment

## Support and Training
### Available Resources
- IT Help Desk for technical support
- Regular training sessions on IT policies
- Online resources and documentation
- One-on-one support for complex issues

### Contact Information
**IT Help Desk**
Email: ithelp@theopencollege.ie
Phone: [Insert number]
Hours: Monday-Friday, 9 AM - 5 PM

Remember: These resources are provided to support your education. Use them responsibly and ethically.
EOD;
    }

    private function getOnlineLearningPolicyContent(): string
    {
        return <<<'EOD'
# Online Learning Platform Policy

## Purpose
This policy provides guidelines for effective and appropriate use of Moodle and other online learning platforms.

## Platform Access
### Login Requirements
- Use only your assigned college credentials
- Never share login information
- Log out when finished, especially on shared computers
- Report access problems to IT Help Desk immediately

### Technical Requirements
- Compatible web browser (Chrome, Firefox, Safari, Edge)
- Stable internet connection
- Updated browser plugins (Flash, Java when required)
- Basic computer skills and troubleshooting knowledge

## Course Participation
### Active Engagement
- Check platform regularly (at least every 48 hours)
- Participate in discussion forums constructively
- Complete activities and assessments on time
- Ask questions when concepts are unclear

### Communication Standards
- Use professional language in all communications
- Be respectful in discussions and feedback
- Respond to direct questions within reasonable timeframes
- Use appropriate discussion forum categories

## Assessment and Submissions
### Submission Guidelines
- Submit work in required formats (PDF, DOC, etc.)
- Follow file naming conventions
- Check submission confirmation
- Keep backup copies of all work

### Technical Issues
- Report problems immediately
- Provide screenshots of error messages
- Contact tutor if deadline affected
- Document all technical difficulties

### Academic Integrity
- Original work only (no plagiarism)
- Proper citation of sources
- No unauthorized collaboration unless specified
- Use plagiarism detection tools responsibly

## Discussion Forums
### Participation Expectations
- Contribute meaningfully to discussions
- Read others' posts before commenting
- Stay on topic and relevant
- Provide constructive feedback to peers

### Prohibited Behavior
- Offensive or inappropriate language
- Personal attacks or harassment
- Off-topic conversations
- Commercial advertising or spam

## Digital Etiquette (Netiquette)
### Written Communication
- Use clear, concise language
- Avoid ALL CAPS (considered shouting)
- Use proper grammar and spelling
- Be patient with others' technical abilities

### Virtual Meetings
- Join on time and prepared
- Mute microphone when not speaking
- Use appropriate backgrounds
- Dress professionally

## Privacy and Confidentiality
### Personal Information
- Protect your personal information
- Don't share other students' information
- Be cautious about what you post publicly
- Report privacy concerns immediately

### Course Content
- Respect intellectual property rights
- Don't share course materials outside class
- Use content only for educational purposes
- Attribute sources properly

## Accessibility
### Inclusive Design
- Platforms designed for accessibility
- Alternative formats available on request
- Closed captioning for video content
- Screen reader compatibility

### Support Services
- Disability Services for accommodation
- Technical support for accessibility issues
- Training on accessibility features
- One-on-one assistance available

## Mobile Learning
### Mobile Apps
- Official college apps only
- Keep apps updated
- Use secure networks when possible
- Same behavioral expectations apply

### Data Usage
- Be aware of data plan limitations
- Use Wi-Fi when available
- Download large files on stable connections
- Monitor data usage regularly

## Troubleshooting
### Common Issues
- Clear browser cache and cookies
- Disable browser extensions temporarily
- Try different browser or device
- Check internet connection stability

### Getting Help
1. Check platform help documentation
2. Search knowledge base for solutions
3. Contact technical support
4. Escalate to course tutor if unresolved

## Content Creation
### Student-Generated Content
- Original work encouraged
- Respect copyright laws
- Use appropriate images and media
- Follow college branding guidelines

### Quality Standards
- Clear, readable formatting
- Appropriate file sizes
- Professional presentation
- Error-free content

## Platform Maintenance
### Scheduled Downtime
- Advance notice provided
- Alternative arrangements made
- Extended deadlines if necessary
- Updates communicated clearly

### Emergency Maintenance
- Immediate notification when possible
- Status updates provided regularly
- Extensions granted automatically
- Alternative submission methods available

Remember: Online learning requires self-discipline and active participation. Take advantage of all available resources and don't hesitate to ask for help when needed.
EOD;
    }

    private function getQualityAssurancePolicyContent(): string
    {
        return <<<'EOD'
# Academic Quality Assurance Policy

## Purpose
This policy establishes our framework for maintaining and continuously improving the quality of education delivered at The Open College.

## Quality Assurance Framework
### Core Principles
- **Student-Centered Approach**: Focus on student learning outcomes
- **Continuous Improvement**: Regular review and enhancement
- **Stakeholder Engagement**: Involving students, staff, and industry
- **Evidence-Based Decisions**: Using data to drive improvements
- **Transparency**: Open communication about quality measures

### Quality Standards
- Alignment with National Framework of Qualifications (NFQ)
- Industry-relevant curriculum content
- Qualified and experienced faculty
- Appropriate learning resources and facilities
- Fair and consistent assessment practices

## Curriculum Development
### Design Process
1. Industry and labor market analysis
2. Learning outcomes definition
3. Curriculum mapping and sequencing
4. Resource requirement assessment
5. Stakeholder consultation and feedback

### Review Cycle
- Annual curriculum review
- Major review every three years
- Immediate updates for critical changes
- Continuous monitoring of relevance

### Approval Process
- Internal academic review committee
- External expert evaluation
- Student representative input
- Management approval
- Regulatory body notification where required

## Teaching and Learning Quality
### Faculty Qualifications
- Relevant academic qualifications
- Professional industry experience
- Teaching qualification or training
- Ongoing professional development
- Regular performance review

### Pedagogical Standards
- Diverse teaching methodologies
- Technology-enhanced learning
- Inclusive educational practices
- Assessment for learning approaches
- Student feedback integration

## Assessment Quality
### Assessment Design
- Valid and reliable measures
- Aligned with learning outcomes
- Variety of assessment methods
- Clear marking criteria
- Appropriate security measures

### Moderation Process
- Internal moderation of all assessments
- External examiner involvement
- Sample marking verification
- Grade distribution analysis
- Appeals and review procedures

## Student Support Services
### Academic Support
- Academic skills development
- Tutoring and mentoring programs
- Library and research support
- IT and technical assistance
- Study skills workshops

### Personal Support
- Student counseling services
- Financial aid and guidance
- Health and wellness programs
- Disability support services
- International student support

## Monitoring and Evaluation
### Key Performance Indicators
- Student satisfaction rates
- Completion and graduation rates
- Employment outcomes
- Grade distribution analysis
- Staff-student ratios

### Data Collection Methods
- Annual student surveys
- Graduate destination surveys
- Employer feedback surveys
- Staff feedback and evaluations
- External examiner reports

### Reporting and Review
- Annual quality report
- Monthly KPI dashboards
- Quarterly review meetings
- External quality audits
- Continuous improvement planning

## Stakeholder Engagement
### Student Voice
- Student representative committees
- Regular feedback surveys
- Focus groups and consultations
- Appeals and complaints procedures
- Graduate feedback collection

### Industry Engagement
- Industry advisory panels
- Work placement partnerships
- Guest lecturer programs
- Employment outcome tracking
- Skills gap analysis

### Staff Development
- Professional development planning
- Teaching and learning training
- Research and scholarship support
- Conference attendance funding
- Peer observation and mentoring

## External Quality Assurance
### Regulatory Compliance
- Quality and Qualifications Ireland (QQI) standards
- Professional body accreditation
- International quality frameworks
- Regular external reviews
- Compliance monitoring

### Benchmarking
- Peer institution comparisons
- International best practice review
- Industry standard alignment
- Student outcome benchmarking
- Continuous competitive analysis

## Continuous Improvement
### Improvement Planning
- Annual improvement targets
- Action plan development
- Resource allocation planning
- Timeline and milestone setting
- Responsibility assignment

### Implementation Monitoring
- Regular progress reviews
- Stakeholder update communications
- Obstacle identification and resolution
- Resource adjustment as needed
- Success celebration and recognition

### Innovation and Development
- Pilot program implementation
- New technology integration
- Teaching methodology experimentation
- Partnership development
- Research and development projects

## Complaints and Appeals
### Informal Resolution
- Open door policy with staff
- Student services consultation
- Peer mediation services
- Early intervention strategies
- Constructive dialogue encouragement

### Formal Procedures
- Written complaint submission
- Investigation and review process
- Independent panel review
- Appeal to external bodies
- Documentation and follow-up

Remember: Quality is everyone's responsibility. We encourage all members of our community to contribute to our continuous improvement efforts.
EOD;
    }
}
