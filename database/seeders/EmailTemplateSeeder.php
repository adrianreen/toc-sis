<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Get first manager user to assign as creator
        $manager = User::where('role', 'manager')->first();
        if (! $manager) {
            $this->command->warn('No manager user found. Skipping email template seeding.');

            return;
        }

        $templates = [
            [
                'name' => 'Student Results with Transcript',
                'subject' => 'Your Academic Results - {{student.name}}',
                'category' => 'academic',
                'description' => 'Email template for sending student results with attached transcript',
                'body_html' => '
                    <h2>Dear {{student.first_name}},</h2>
                    
                    <p>We are pleased to share your academic results for your programme: <strong>{{programme.title}}</strong>.</p>
                    
                    <div class="student-details">
                        <h3>Student Information</h3>
                        <div class="detail-row">
                            <span class="detail-label">Student Number:</span>
                            <span class="detail-value">{{student.student_number}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Programme:</span>
                            <span class="detail-value">{{programme.title}} ({{programme.awarding_body}})</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Intake:</span>
                            <span class="detail-value">{{programme_instance.label}}</span>
                        </div>
                    </div>
                    
                    <p>Please find your official academic transcript attached to this email. This document contains your complete academic record including all completed assessments and grades.</p>
                    
                    <div class="info-box">
                        <p><strong>Important:</strong> Please keep this transcript safe as it serves as your official academic record. If you need additional copies, you can download them from your student portal.</p>
                    </div>
                    
                    <p>If you have any questions about your results, please don\'t hesitate to contact us.</p>
                    
                    <a href="{{portal_url}}" class="btn">Access Student Portal</a>
                    
                    <p>Congratulations on your academic progress!</p>
                    
                    <p>Best regards,<br>
                    {{sender.name}}<br>
                    {{college.name}}</p>
                ',
                'body_text' => '
Dear {{student.first_name}},

We are pleased to share your academic results for your programme: {{programme.title}}.

Student Information:
- Student Number: {{student.student_number}}
- Programme: {{programme.title}} ({{programme.awarding_body}})
- Intake: {{programme_instance.label}}

Please find your official academic transcript attached to this email. This document contains your complete academic record including all completed assessments and grades.

IMPORTANT: Please keep this transcript safe as it serves as your official academic record. If you need additional copies, you can download them from your student portal at {{portal_url}}.

If you have any questions about your results, please don\'t hesitate to contact us.

Congratulations on your academic progress!

Best regards,
{{sender.name}}
{{college.name}}
                ',
                'system_template' => true,
            ],
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{college.name}} - {{student.name}}',
                'category' => 'administrative',
                'description' => 'Welcome email for new students with programme information and next steps',
                'body_html' => '
                    <h2>Welcome to {{college.name}}, {{student.first_name}}!</h2>
                    
                    <p>We are delighted to welcome you to {{college.name}} and congratulate you on taking this important step in your educational journey.</p>
                    
                    <div class="student-details">
                        <h3>Your Programme Details</h3>
                        <div class="detail-row">
                            <span class="detail-label">Student Number:</span>
                            <span class="detail-value">{{student.student_number}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Programme:</span>
                            <span class="detail-value">{{programme.title}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Intake:</span>
                            <span class="detail-value">{{programme_instance.label}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Start Date:</span>
                            <span class="detail-value">{{programme_instance.intake_start_date}}</span>
                        </div>
                    </div>
                    
                    <h3>Getting Started</h3>
                    <p>Here are the next steps to begin your studies:</p>
                    
                    <ol>
                        <li><strong>Access Your Student Portal:</strong> Login to track your progress and access resources</li>
                        <li><strong>Review Programme Information:</strong> Familiarize yourself with your modules and assessments</li>
                        <li><strong>Contact Your Programme Coordinator:</strong> If you have any questions</li>
                    </ol>
                    
                    <div class="info-box">
                        <p><strong>Student Portal Access:</strong> You can access your student portal using your email address. If you haven\'t received login instructions, please contact us immediately.</p>
                    </div>
                    
                    <a href="{{portal_url}}" class="btn">Access Student Portal</a>
                    
                    <p>We look forward to supporting you throughout your studies. Welcome aboard!</p>
                    
                    <p>Best regards,<br>
                    {{sender.name}}<br>
                    {{college.name}}</p>
                ',
                'body_text' => '
Welcome to {{college.name}}, {{student.first_name}}!

We are delighted to welcome you to {{college.name}} and congratulate you on taking this important step in your educational journey.

Your Programme Details:
- Student Number: {{student.student_number}}
- Programme: {{programme.title}}
- Intake: {{programme_instance.label}}
- Start Date: {{programme_instance.intake_start_date}}

Getting Started:
Here are the next steps to begin your studies:

1. Access Your Student Portal: Login to track your progress and access resources
2. Review Programme Information: Familiarize yourself with your modules and assessments  
3. Contact Your Programme Coordinator: If you have any questions

Student Portal Access: You can access your student portal at {{portal_url}} using your email address. If you haven\'t received login instructions, please contact us immediately.

We look forward to supporting you throughout your studies. Welcome aboard!

Best regards,
{{sender.name}}
{{college.name}}
                ',
                'system_template' => true,
            ],
            [
                'name' => 'Assessment Reminder',
                'subject' => 'Assessment Deadline Reminder - {{student.name}}',
                'category' => 'academic',
                'description' => 'Reminder email for upcoming assessment deadlines',
                'body_html' => '
                    <h2>Assessment Deadline Reminder</h2>
                    
                    <p>Dear {{student.first_name}},</p>
                    
                    <p>This is a friendly reminder about an upcoming assessment deadline for your programme.</p>
                    
                    <div class="warning-box">
                        <p><strong>Action Required:</strong> Please ensure you submit your assessment on time to avoid any academic penalties.</p>
                    </div>
                    
                    <div class="student-details">
                        <h3>Assessment Details</h3>
                        <div class="detail-row">
                            <span class="detail-label">Student:</span>
                            <span class="detail-value">{{student.name}} ({{student.student_number}})</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Programme:</span>
                            <span class="detail-value">{{programme.title}}</span>
                        </div>
                    </div>
                    
                    <h3>Next Steps</h3>
                    <ul>
                        <li>Review your assessment requirements</li>
                        <li>Ensure you have all necessary materials</li>
                        <li>Submit before the deadline</li>
                        <li>Contact us if you need support</li>
                    </ul>
                    
                    <p>If you need any assistance or have questions about your assessment, please don\'t hesitate to reach out to your programme coordinator.</p>
                    
                    <a href="{{progress_link}}" class="btn">View My Progress</a>
                    
                    <p>Best of luck with your assessment!</p>
                    
                    <p>Best regards,<br>
                    {{sender.name}}<br>
                    {{college.name}}</p>
                ',
                'body_text' => '
Assessment Deadline Reminder

Dear {{student.first_name}},

This is a friendly reminder about an upcoming assessment deadline for your programme.

ACTION REQUIRED: Please ensure you submit your assessment on time to avoid any academic penalties.

Assessment Details:
- Student: {{student.name}} ({{student.student_number}})
- Programme: {{programme.title}}

Next Steps:
- Review your assessment requirements
- Ensure you have all necessary materials
- Submit before the deadline
- Contact us if you need support

If you need any assistance or have questions about your assessment, please don\'t hesitate to reach out to your programme coordinator.

You can view your progress at: {{progress_link}}

Best of luck with your assessment!

Best regards,
{{sender.name}}
{{college.name}}
                ',
                'system_template' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            $templateData['created_by'] = $manager->id;
            $templateData['is_active'] = true;

            EmailTemplate::updateOrCreate(
                ['name' => $templateData['name']],
                $templateData
            );
        }

        $this->command->info('Created '.count($templates).' default email templates.');
    }
}
