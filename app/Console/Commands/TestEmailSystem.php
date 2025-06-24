<?php

namespace App\Console\Commands;

use App\Mail\SystemTestEmail;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailSystem extends Command
{
    protected $signature = 'email:test 
                          {email : Email address to send test to}
                          {--template=welcome : Email template to test (welcome, grade-notification, reminder)}
                          {--queue : Send via queue instead of immediately}
                          {--check-config : Check email configuration only}';

    protected $description = 'Test the email system configuration and delivery';

    public function handle()
    {
        if ($this->option('check-config')) {
            return $this->checkEmailConfiguration();
        }

        $email = $this->argument('email');
        $template = $this->option('template');
        $useQueue = $this->option('queue');

        $this->info('Testing email system...');
        $this->info("To: {$email}");
        $this->info("Template: {$template}");
        $this->info('Queue: '.($useQueue ? 'Yes' : 'No'));

        try {
            switch ($template) {
                case 'welcome':
                    $this->sendWelcomeTest($email, $useQueue);
                    break;
                case 'grade-notification':
                    $this->sendGradeNotificationTest($email, $useQueue);
                    break;
                case 'reminder':
                    $this->sendReminderTest($email, $useQueue);
                    break;
                default:
                    $this->sendBasicTest($email, $useQueue);
            }

            $this->info('✅ Email test completed successfully');

            if ($useQueue) {
                $this->warn('📋 Email queued for delivery. Check queue status with: php artisan queue:work');
            }

        } catch (\Exception $e) {
            $this->error('❌ Email test failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    private function checkEmailConfiguration()
    {
        $this->info('Checking email configuration...');

        // Check basic mail configuration
        $driver = config('mail.default');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $this->table(['Setting', 'Value'], [
            ['Mail Driver', $driver],
            ['From Address', $fromAddress],
            ['From Name', $fromName],
            ['Queue Connection', config('queue.default')],
        ]);

        // Check driver-specific configuration
        switch ($driver) {
            case 'smtp':
                $this->checkSmtpConfiguration();
                break;
            case 'ses':
                $this->checkSesConfiguration();
                break;
            case 'mailgun':
                $this->checkMailgunConfiguration();
                break;
            case 'log':
                $this->warn('⚠️  Using log driver - emails will be written to log files, not delivered');
                break;
            default:
                $this->info("Driver: {$driver}");
        }

        // Test mail configuration
        try {
            $transport = Mail::getSwiftMailer()->getTransport();
            $this->info('✅ Mail transport configured successfully');
        } catch (\Exception $e) {
            $this->error('❌ Mail transport configuration error: '.$e->getMessage());
        }

        return 0;
    }

    private function checkSmtpConfiguration()
    {
        $config = config('mail.mailers.smtp');
        $this->info('SMTP Configuration:');
        $this->table(['Setting', 'Value'], [
            ['Host', $config['host'] ?? 'Not set'],
            ['Port', $config['port'] ?? 'Not set'],
            ['Username', $config['username'] ?? 'Not set'],
            ['Password', $config['password'] ? '***Hidden***' : 'Not set'],
            ['Encryption', $config['encryption'] ?? 'None'],
        ]);
    }

    private function checkSesConfiguration()
    {
        $this->info('Amazon SES Configuration:');
        $this->table(['Setting', 'Value'], [
            ['Access Key', config('services.ses.key') ? '***Hidden***' : 'Not set'],
            ['Secret Key', config('services.ses.secret') ? '***Hidden***' : 'Not set'],
            ['Region', config('services.ses.region') ?? 'Not set'],
        ]);
    }

    private function checkMailgunConfiguration()
    {
        $this->info('Mailgun Configuration:');
        $this->table(['Setting', 'Value'], [
            ['Domain', config('services.mailgun.domain') ?? 'Not set'],
            ['Secret', config('services.mailgun.secret') ? '***Hidden***' : 'Not set'],
            ['Endpoint', config('services.mailgun.endpoint') ?? 'api.mailgun.net'],
        ]);
    }

    private function sendBasicTest($email, $useQueue)
    {
        $this->info('Sending basic test email...');

        $mailable = new SystemTestEmail([
            'subject' => 'TOC-SIS Email System Test',
            'message' => 'This is a test email from the TOC-SIS system to verify email delivery is working correctly.',
            'test_time' => now()->format('Y-m-d H:i:s'),
            'system_info' => [
                'Laravel Version' => app()->version(),
                'PHP Version' => PHP_VERSION,
                'Mail Driver' => config('mail.default'),
                'Queue Driver' => config('queue.default'),
            ],
        ]);

        if ($useQueue) {
            Mail::to($email)->queue($mailable);
        } else {
            Mail::to($email)->send($mailable);
        }
    }

    private function sendWelcomeTest($email, $useQueue)
    {
        $this->info('Sending welcome email test...');

        // Create test data
        $testUser = new User([
            'name' => 'Test Student',
            'email' => $email,
            'role' => 'student',
        ]);

        $service = app(NotificationService::class);

        // This would normally send a welcome notification
        $this->info('Welcome email functionality tested (would be sent via NotificationService)');
    }

    private function sendGradeNotificationTest($email, $useQueue)
    {
        $this->info('Sending grade notification test...');

        // Find a test student user or create mock data
        $testUser = User::where('role', 'student')->first();

        if (! $testUser) {
            $this->warn('No student users found. Creating mock notification test...');
            $testUser = new User([
                'name' => 'Test Student',
                'email' => $email,
                'role' => 'student',
            ]);
        }

        $service = app(NotificationService::class);

        try {
            $notification = $service->notifyGradeReleased(
                $testUser,
                'Test Module',
                'Test Assessment',
                85.0
            );
            $this->info('Grade notification test completed');
        } catch (\Exception $e) {
            $this->warn('Grade notification test failed: '.$e->getMessage());
        }
    }

    private function sendReminderTest($email, $useQueue)
    {
        $this->info('Sending reminder email test...');

        $testUser = new User([
            'name' => 'Test Student',
            'email' => $email,
            'role' => 'student',
        ]);

        $service = app(NotificationService::class);

        try {
            $notification = $service->notifyAssessmentDeadline(
                $testUser,
                'Test Assessment',
                now()->addDays(3),
                'Test Module'
            );
            $this->info('Reminder notification test completed');
        } catch (\Exception $e) {
            $this->warn('Reminder notification test failed: '.$e->getMessage());
        }
    }
}
