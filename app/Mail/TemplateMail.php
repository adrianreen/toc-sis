<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailTemplate $template,
        public Student $student,
        public User $sender,
        public array $customVariables = [],
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null
    ) {}

    public function build()
    {
        $processed = $this->template->replaceVariables(
            $this->student, 
            $this->sender, 
            $this->customVariables
        );

        $mail = $this->subject($processed['subject'])
                     ->view('mail.template', [
                         'content' => $processed['body_html'],
                         'student' => $this->student,
                         'sender' => $this->sender,
                         'subject' => $processed['subject'],
                         'recipient_email' => $this->student->email,
                     ]);

        // Add text version if available
        if (!empty($processed['body_text'])) {
            $mail->text('mail.template-text', [
                'content' => $processed['body_text'],
                'student' => $this->student,
                'sender' => $this->sender,
            ]);
        }

        // Add attachment if provided
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $mail->attach($this->attachmentPath, [
                'as' => $this->attachmentName ?? basename($this->attachmentPath),
                'mime' => mime_content_type($this->attachmentPath),
            ]);
        }

        return $mail;
    }
}